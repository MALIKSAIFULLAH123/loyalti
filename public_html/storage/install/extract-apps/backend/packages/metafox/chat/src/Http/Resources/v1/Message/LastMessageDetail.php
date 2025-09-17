<?php

namespace MetaFox\Chat\Http\Resources\v1\Message;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Chat\Models\Message;
use MetaFox\User\Http\Resources\v1\User\UserSimple;

/**
 * Class MessageDetail.
 * @property Message $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class LastMessageDetail extends JsonResource
{
    /**
     * @param mixed $request
     *
     * @return array<mixed>
     */
    public function toArray($request): array
    {
        $user = $this->resource->userEntity?->detail;

        return [
            'id'          => $this->resource->entityId(),
            'type'        => $this->resource->type,
            'message'     => ban_word()->clean($this->resource->message),
            'room_id'     => $this->resource->room_id,
            'user_id'     => $this->resource->user_id,
            'user_type'   => $this->resource->user_type,
            'user'        => $user ? new UserSimple($user) : null,
            'attachments' => new MessageAttachmentCollection($this->resource->attachments),
            'extra'       => json_decode($this->resource->extra),
            'created_at'  => $this->resource->created_at,
            'updated_at'  => $this->resource->updated_at,
        ];
    }
}
