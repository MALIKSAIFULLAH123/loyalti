<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

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

class EventEmbedCollection extends ResourceCollection
{
    public $collects = EventEmbed::class;
}
