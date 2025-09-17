<?php

namespace MetaFox\Story\Http\Resources\v1\StoryReaction;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Story\Models\StoryReaction as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StoryReactionDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class StoryReactionDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $reaction = $this->resource
            ->reactionData()
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->pluck('item')
            ->toBase();

        $resource = $reaction->first();

        if (!user()->can('view', $resource)) {
            return [];
        }

        return ResourceGate::items($reaction, false);
    }
}
