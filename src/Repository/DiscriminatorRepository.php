<?php

namespace Zus1\Discriminator\Repository;

use Zus1\Discriminator\Helper\ModelHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class DiscriminatorRepository
{
    public function __construct(
        private ModelHelper $parentModelHelper,
    ){
    }

    public function findChild(Model $user): ?Model
    {
        /** @var ?Model $child */
        $child = method_exists($user, 'child') ? $user->child()->first() : null;

        return $child;
    }

    public function findParent(Model $user): ?Model
    {
        /** @var ?Model $parent */
        $parent = method_exists($user, 'parent') ? $user->parent()->first() : null;

        return $parent;
    }

    public function applyFilters(Builder $builder, array $filters): void
    {
        if($filters === []) {
            return;
        }

        [$parentProperties, $properties] = $this->getProperties($builder, $filters);

        if($parentProperties === []) {
            foreach ($filters as $filter => $value) {
                $builder->where($filter, $value);
            }

            return;
        }

        $this->applyJoinQuery($builder);

        [$modelTable, $parentTable] = $this->getTableNames($builder);

        foreach($parentProperties as $property) {
            $builder->where(sprintf('%s.%s', $parentTable, $property), $filters[$property]);
        }
        foreach($properties as $property) {
            $builder->where(sprintf('%s.%s', $modelTable, $property), $filters[$property]);
        }
    }

    public function applyOrderBy(Builder $builder, string $orderBy, string $orderDirection): void
    {
        $model = $builder->getModel();
        $parent = $this->parentModelHelper->newParent($model);
        $parentProperties = $parent->getConnection()->getSchemaBuilder()->getColumnListing($parent->getTable());
        $sharedFields = $parent->getDiscriminatorSharedFields();

        if(!in_array($orderBy, $parentProperties) && !in_array($orderBy, $sharedFields)) {
            $builder->orderBy($orderBy, $orderDirection);

            return;
        }

        [$modelTable, $parentTable] = $this->getTableNames($builder);

        if(in_array($orderBy, $sharedFields)) {
            $builder->orderBy(sprintf('%s.%s', $modelTable, $orderBy), $orderDirection);

            return;
        }

        if($builder->getQuery()->joins === null) {
            $this->applyJoinQuery($builder);
        }

        $builder->orderBy(sprintf('%s.%s', $parentTable, $orderBy), $orderDirection);
    }

    private function applyJoinQuery(Builder $builder): void
    {
        $model = $builder->getModel();
        [$modelTable, $parentTable] = $this->getTableNames($builder);

        $builder->join($parentTable, function (JoinClause $join) use ($modelTable, $parentTable, $model) {
            $join->on(sprintf('%s.id', $modelTable), sprintf('%s.child_id', $parentTable))
                ->where(sprintf('%s.child_type', $parentTable), $model::class);

        })->select(sprintf('%s.*', $modelTable));
    }

    private function getProperties(Builder $builder, array $filters = []): array
    {
        $model = $builder->getModel();
        $rawProperties = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());

        if($filters === []) {
            $parentProperties = $rawProperties;
        } else {
            $parentProperties = array_diff(array_keys($filters), $rawProperties);
        }

        $properties = array_values(array_diff(array_keys($filters), $parentProperties));

        return [$parentProperties, $properties];
    }

    private function getTableNames(Builder $builder): array
    {
        $model = $builder->getModel();

        $modelTable = $model->getTable();
        $parentTable = $this->parentModelHelper->newParent($model)->getTable();

        return [$modelTable, $parentTable];
    }

}
