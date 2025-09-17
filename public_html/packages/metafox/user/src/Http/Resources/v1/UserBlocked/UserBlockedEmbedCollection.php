<?php

namespace MetaFox\User\Http\Resources\v1\UserBlocked;

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

class UserBlockedEmbedCollection extends ResourceCollection
{
    public $collects = UserBlockedEmbed::class;
}
