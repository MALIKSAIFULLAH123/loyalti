<?php

namespace MetaFox\Photo\Http\Resources\v1\AlbumItem;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/*
|--------------------------------------------------------------------------
| Resource Collection
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

class AlbumItemItemCollection extends ResourceCollection
{
    public $collects = AlbumItemItem::class;
}
