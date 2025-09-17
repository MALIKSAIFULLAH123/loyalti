<?php

namespace MetaFox\User\Http\Resources\v1\User;

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

class UserEmbedCollection extends ResourceCollection
{
    public $collects = UserEmbed::class;
}
