<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll;

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

class PollEmbedCollection extends ResourceCollection
{
    public $collects = PollEmbed::class;
}
