<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection;

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

class BgsCollectionEmbedCollection extends ResourceCollection
{
    public $collects = BgsCollectionEmbed::class;
}
