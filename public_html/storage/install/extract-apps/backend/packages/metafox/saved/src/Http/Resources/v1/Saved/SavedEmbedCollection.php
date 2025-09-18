<?php

namespace MetaFox\Saved\Http\Resources\v1\Saved;

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

class SavedEmbedCollection extends ResourceCollection
{
    public $collects = SavedEmbed::class;
}
