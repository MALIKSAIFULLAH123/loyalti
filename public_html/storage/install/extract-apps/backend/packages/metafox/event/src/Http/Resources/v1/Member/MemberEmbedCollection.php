<?php

namespace MetaFox\Event\Http\Resources\v1\Member;

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

class MemberEmbedCollection extends ResourceCollection
{
    public $collects = MemberEmbed::class;
}
