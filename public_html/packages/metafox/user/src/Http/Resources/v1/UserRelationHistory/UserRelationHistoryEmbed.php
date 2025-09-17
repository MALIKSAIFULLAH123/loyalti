<?php

namespace MetaFox\User\Http\Resources\v1\UserRelationHistory;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Models\UserRelationHistory;

/**
 * Class UserRelationHistoryEmbed.
 * @property UserRelationHistory $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserRelationHistoryEmbed extends JsonResource
{
    public function toArray($request)
    {
        $relationWithUser = null;

        if ($this->resource->relation_with) {
            $relationWithUser = $this->resource->relationWithUser;

            if (null !== $relationWithUser) {
                $relationWithUser = ResourceGate::asEmbed($this->resource->relationWithUser);
            }
        }

        return [
            'id'            => $this->resource->entityId(),
            'resource_name' => $this->resource->entityType(),
            'module_name'   => 'user',
            'info'          => 'user_name_updated_their_relationship',
            'user'          => ResourceGate::asEmbed($this->resource->user),
            'relation'      => [
                'label' => $this->resource->relationship_text,
                'value' => $this->resource->relation_id,
            ],
            'relation_image'      => $this->resource->relationship?->avatar,
            'relation_image_dark' => $this->resource->relationship?->avatar_dark,
            'relation_with'       => $relationWithUser,
            'gender'              => $this->resource->gender,
            'creation_date'       => Carbon::parse($this->resource->created_at)->toISOString(),
            'modification_date'   => Carbon::parse($this->resource->updated_at)->toISOString(),
            'privacy'             => MetaFoxPrivacy::EVERYONE,
            'shared_item_type'    => $this->resource->activity_feed?->entityType(),
            'shared_item_id'      => $this->resource->activity_feed?->entityId(),
        ];
    }
}
