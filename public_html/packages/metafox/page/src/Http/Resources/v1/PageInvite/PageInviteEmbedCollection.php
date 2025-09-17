<?php

namespace MetaFox\Page\Http\Resources\v1\PageInvite;

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

class PageInviteEmbedCollection extends ResourceCollection
{
    public $collects = PageInviteEmbed::class;
}
