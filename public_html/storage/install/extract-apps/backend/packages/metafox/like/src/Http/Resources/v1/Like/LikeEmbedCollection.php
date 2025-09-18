<?php

namespace MetaFox\Like\Http\Resources\v1\Like;

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

class LikeEmbedCollection extends ResourceCollection
{
    public $collects = LikeEmbed::class;
}
