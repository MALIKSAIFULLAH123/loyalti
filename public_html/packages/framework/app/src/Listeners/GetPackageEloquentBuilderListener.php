<?php

namespace MetaFox\App\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\App\Repositories\PackageRepositoryInterface;

class GetPackageEloquentBuilderListener
{
    public function handle(): Builder
    {
        return resolve(PackageRepositoryInterface::class)->getEloquentBuilder();
    }
}
