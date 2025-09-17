<?php

namespace MetaFox\Chat\Http\Resources\v1\Message;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Chat\Models\Message;
use MetaFox\Chat\Traits\MessageTraits;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class MessageDetail.
 * @property Message $resource
 */
class RelatedMessageItem extends JsonResource
{
    use MessageTraits;

    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'chat',
            'resource_name' => 'message',
            'type'          => $this->resource->type,
            'message'       => ban_word()->clean($this->resource->message),
            'room_id'       => $this->resource->room_id,
            'user_id'       => $this->resource->user_id,
            'user_type'     => $this->resource->user_type,
            'user'          => ResourceGate::user($this->resource->userEntity),
            'attachments'   => new MessageAttachmentCollection($this->resource->attachments),
            'extra'         => $this->processExtraMessage($this->resource->extra),
            'reactions'     => $this->normalizeReactions($this->resource->reactions),
            'created_at'    => $this->resource->created_at,
            'updated_at'    => $this->resource->updated_at,
        ];
    }
}
