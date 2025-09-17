<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\SiteSettingRepositoryInterface;

class GetSettingEloquentBuilderListener
{
    public function handle(): Builder
    {
        return resolve(SiteSettingRepositoryInterface::class)->getEloquentBuilder();
    }
}
