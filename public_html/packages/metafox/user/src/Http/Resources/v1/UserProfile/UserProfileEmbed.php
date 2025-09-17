<?php

namespace MetaFox\User\Http\Resources\v1\UserProfile;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Models\UserProfile;

/**
 * Class UserProfileEmbed.
 * @property UserProfile $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserProfileEmbed extends JsonResource
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
            'user'          => ResourceGate::asEmbed($this->resource->user),
            'relation'      => [
                'label' => $this->resource->relationship_text,
                'value' => $this->resource->relation,
            ],
            'relation_image'      => $this->resource->relationship?->avatar,
            'relation_image_dark' => $this->resource->relationship?->avatar_dark,
            'relation_with'       => $relationWithUser,
            'gender'              => $this->resource->gender,
        ];
    }
}
