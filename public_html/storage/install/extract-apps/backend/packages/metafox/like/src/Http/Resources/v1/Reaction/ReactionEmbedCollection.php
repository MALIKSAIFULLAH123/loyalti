<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction;

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

class ReactionEmbedCollection extends ResourceCollection
{
    public $collects = ReactionEmbed::class;
}
