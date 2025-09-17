<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

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

class FriendEmbedCollection extends ResourceCollection
{
    public $collects = FriendEmbed::class;
}
