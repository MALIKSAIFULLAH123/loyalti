<?php

namespace MetaFox\Hashtag\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Hashtag\Http\Resources\v1\Hashtag\HashtagSuggestion;
use MetaFox\Hashtag\Models\Tag as Model;

class GetSearchResourceListener
{
    /**
     * @param  Model        $resource
     * @return JsonResource
     */
    public function handle(Model $resource): JsonResource
    {
        return new HashtagSuggestion($resource);
    }
}
