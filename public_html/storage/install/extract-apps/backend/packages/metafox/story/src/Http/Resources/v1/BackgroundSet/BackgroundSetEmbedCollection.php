<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet;

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

class BackgroundSetEmbedCollection extends ResourceCollection
{
    public $collects = BackgroundSetEmbed::class;
}
