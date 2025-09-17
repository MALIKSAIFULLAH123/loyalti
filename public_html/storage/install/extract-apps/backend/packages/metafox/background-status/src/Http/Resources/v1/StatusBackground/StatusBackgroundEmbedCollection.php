<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\StatusBackground;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/*
|--------------------------------------------------------------------------
| Resource Collection
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

class StatusBackgroundEmbedCollection extends ResourceCollection
{
    public $collects = StatusBackgroundEmbed::class;
}
