<?php

namespace MetaFox\Sticker\Http\Resources\v1\Sticker;

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

class StickerEmbedCollection extends ResourceCollection
{
    public $collects = StickerEmbed::class;
}
