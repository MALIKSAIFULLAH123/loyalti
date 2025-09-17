<?php

namespace MetaFox\User\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\User\Http\Resources\v1\User\UserSimple;
use MetaFox\User\Models\User as Model;

class GetSearchResourceListener
{
    /**
     * @param  Model        $resource
     * @return JsonResource
     */
    public function handle(Model $resource): JsonResource
    {
        return new UserSimple($resource);
    }
}
