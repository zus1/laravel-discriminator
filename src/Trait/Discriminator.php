<?php

namespace Zus1\Discriminator\Trait;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait Discriminator
{
    public function child(): ?MorphTo
    {
        return method_exists($this, 'morphTo') ? $this->morphTo(): null;
    }

    public function parent(): ?MorphOne
    {
        return method_exists($this, 'morphOne') ? $this->morphOne(User::class, 'child'): null;
    }
}
