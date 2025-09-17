<?php

namespace MetaFox\Activity\Http\Resources\v1\Share;

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

class ShareEmbedCollection extends ResourceCollection
{
    public $collects = ShareEmbed::class;
}
