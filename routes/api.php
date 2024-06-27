<?php

use Illuminate\Support\Facades\Route;

Route::get('discriminators/user-types', \Zus1\Discriminator\Controllers\AvailableUserTypes::class);
