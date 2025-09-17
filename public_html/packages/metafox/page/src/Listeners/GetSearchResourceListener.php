<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Http\Resources\v1\Page\PageSimple;
use MetaFox\Page\Models\Page as Model;

class GetSearchResourceListener
{
    /**
     * @param  Model        $resource
     * @return JsonResource
     */
    public function handle(Model $resource): JsonResource
    {
        return new PageSimple($resource);
    }
}
