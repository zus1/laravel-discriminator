<?php

namespace Zus1\Discriminator\Interface;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Discriminator
{
    public function parent(): MorphOne;
}
