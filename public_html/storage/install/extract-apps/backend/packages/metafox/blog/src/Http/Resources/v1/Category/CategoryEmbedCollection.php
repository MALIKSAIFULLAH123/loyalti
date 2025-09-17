<?php

namespace MetaFox\Blog\Http\Resources\v1\Category;

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

class CategoryEmbedCollection extends ResourceCollection
{
    public $collects = CategoryEmbed::class;
}
