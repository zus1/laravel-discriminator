<?php

namespace Zus1\Discriminator\Controllers;

use Zus1\Discriminator\Helper\Helper;
use Illuminate\Http\JsonResponse;

class AvailableUserTypes
{
    public function __construct(
        private Helper $helper,
    ){
    }

    public function __invoke(): JsonResponse
    {
        $types = $this->helper->getAvailableUserTypes();

        return new JsonResponse($types);
    }
}
