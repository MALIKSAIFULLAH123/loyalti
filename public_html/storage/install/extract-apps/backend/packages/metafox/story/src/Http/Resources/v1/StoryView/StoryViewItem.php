<?php

namespace MetaFox\Story\Http\Resources\v1\StoryView;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryView as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class StoryViewItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class StoryViewItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $reactions = $this->resource->reaction()
            ->where('story_reactions.user_id', $this->userId())->first();

        $resReactions = ResourceGate::item($reactions, false);

        return array_merge(ResourceGate::user($this->resource->userEntity), [
            'id'            => $this->resource->entityId(),
            'user_id'       => $this->resource->userId(),
            'module_name'   => Story::ENTITY_TYPE,
            'resource_name' => 'user_story_viewer',
            'reactions'     => $resReactions,
            'message'       => [],
        ]);
    }
}
