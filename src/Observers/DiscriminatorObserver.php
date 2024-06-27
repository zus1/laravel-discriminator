<?php

namespace Zus1\Discriminator\Observers;

use Zus1\Discriminator\Interface\Discriminator;
use Zus1\Discriminator\Helper\ModelHelper;
use Illuminate\Database\Eloquent\Model;

class DiscriminatorObserver
{
    private static array $_parentAttributes = [];

    private ?Model $parent = null;

    public function __construct(
        private ModelHelper $parentModelHelper,
    ){
    }

    public function creating(Model $model): void
    {
        $this->divideAttributes($model);
    }

    /**
     * Handle the Student "created" event.
     */
    public function created(Model $model): void
    {
        $this->saveParent($model);

        $this->mergeWithParent($model);
    }

    public function retrieved(Model $model): void
    {
        $this->mergeWithParent($model);
    }

    public function updating(Model $model): void
    {
        $this->divideAttributes($model);
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->updateParent($model);

        $this->mergeWithParent($model);
    }

    public function deleting(Model $model): void
    {
        $parent = $this->parentModelHelper->loadParent($model);

        $parent->delete();
    }

    public function forceDeleting(Model $model): void
    {
        $parent = $this->parentModelHelper->newParent($model);

        $parent->forceDelete();
    }

    private function saveParent(Model|Discriminator $model): void
    {
        $parent = $this->parentModelHelper->newParent($model);

        $parent->setRawAttributes(self::$_parentAttributes);

        $model->parent()->save($parent);
    }

    private function updateParent(Model|Discriminator $model): void
    {
;        $parent = $this->parentModelHelper->loadParent($model);

        $parent->setRawAttributes(array_filter(self::$_parentAttributes, function ($key) {
            return $key !=='id';
        }, ARRAY_FILTER_USE_KEY));

        $parent->save();
    }

    private function mergeWithParent(Model|Discriminator $model): void
    {
        $model->setRawAttributes([
            ...$this->parentModelHelper->loadParent($model)->getAttributes(),
            ...$model->getAttributes(),
        ]);
    }

    private function divideAttributes(Model $model): void
    {
        $parent = $this->parentModelHelper->newParent($model);
        $parentProperties = $parent->getConnection()->getSchemaBuilder()->getColumnListing($parent->getTable());

        $attributes = $model->getAttributes();
        $parentAttributes =  [];

        foreach($attributes as $attribute => $value) {
            if(in_array($attribute, $parentProperties)) {
                if($attribute === 'id') {
                    continue;
                }
                $parentAttributes[$attribute] = $value;
                unset($attributes[$attribute]);
            }
        }

        self::$_parentAttributes = $parentAttributes;

        $model->setRawAttributes($attributes);
    }
}
