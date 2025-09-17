<?php

namespace MetaFox\Comment\Http\Resources\v1\Comment;

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

class CommentEmbedCollection extends ResourceCollection
{
    public $collects = CommentEmbed::class;
}
