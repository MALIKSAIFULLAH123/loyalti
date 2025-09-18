<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Http\Resources\v1\Group\GroupSimple;
use MetaFox\Group\Models\Group as Model;

/**
 * Class GetSearchResourceListener.
 * @ignore
 */
class GetSearchResourceListener
{
    /**
     * @param  Model        $resource
     * @return JsonResource
     */
    public function handle(Model $resource): JsonResource
    {
        return new GroupSimple($resource);
    }
}
