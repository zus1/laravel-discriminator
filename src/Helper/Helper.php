<?php

namespace Zus1\Discriminator\Helper;

class Helper
{
    public function getAvailableUserTypes(): array
    {
        if(!is_dir(($dir = app_path('Models/Users')))) {
            throw new \Exception(sprintf('Directory %s do not exist', $dir), 500);
        }

        return array_values(array_filter(array_map(function (string $file) {
            return lcfirst(explode('.', $file)[0]);
        }, scandir(app_path('Models/Users'))), fn(string $value) => $value !== ''));
    }
}
