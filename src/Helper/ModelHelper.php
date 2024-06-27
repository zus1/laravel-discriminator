<?php

namespace Zus1\Discriminator\Helper;

use Illuminate\Database\Eloquent\Model;
use Zus1\Discriminator\Interface\Discriminator;

class ModelHelper
{
    private ?Model $parent = null;

    public function newParent(Model $model): Model
    {
        $rf = new \ReflectionClass($model);
        $parentClass = $rf->getParentClass()->getName();

        /** @var Model $parent */
        $parent = new $parentClass();

        return $parent;
    }

    public function loadParent(Model|Discriminator $model): Model
    {
        if($this->parent === null) {
            $this->parent = $model->parent()->first();
        }

        return $this->parent;
    }

    public function getParent(): Model
    {
        return $this->parent;
    }

    public function copyModelAttributes(Model $model): array
    {
        $copyAttributes = $model->getAttributes();

        unset($copyAttributes['id']);

        return $copyAttributes;
    }

    public function cloneModel(Model $model): Model
    {
        $clone = clone $model;

        unset($clone->id);

        return $clone;
    }
}
