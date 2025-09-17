<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway;

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

class GatewayEmbedCollection extends ResourceCollection
{
    public $collects = GatewayEmbed::class;
}
