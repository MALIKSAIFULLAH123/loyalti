<?php

namespace MetaFox\HealthCheck\Resolver;

use Illuminate\Support\Facades\File;
use MetaFox\Platform\HealthCheck\Resolver;

class UnexpectedDirectory extends Resolver
{
    public function resolve(): bool
    {
        if (!File::exists(base_path('public/install'))) {
            return false;
        }

        File::deleteDirectory(base_path('public/install'));

        return true;
    }

    public function successMessage(): string
    {
        return __p('health-check::phrase.public_install_folder_successfully_removed');
    }
    public function errorMessage(): string
    {
        return __p('health-check::phrase.public_install_folder_failed_to_be_removed');
    }
}
