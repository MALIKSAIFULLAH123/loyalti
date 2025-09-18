<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Poll\Http\Resources\v1\Traits\PollHasExtra;
use MetaFox\Poll\Models\Poll as Model;
use MetaFox\Poll\Support\Facade\Poll;

/**
 * Class PollItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PollItem extends JsonResource
{
    use PollHasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $shortDescription = '';
        if ($this->resource->pollText) {
            $shortDescription = parse_output()->getDescription($this->resource->pollText->text_parsed);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'question'          => $this->resource->question,
            'description'       => $shortDescription,
            'module_id'         => $this->resource->entityType(),
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'is_featured'       => $this->resource->is_featured,
            'is_sponsored'      => $this->resource->is_sponsor,
            'is_closed'         => $this->resource->is_closed,
            'status_text'       => Poll::getStatusTexts($this->resource),
            'close_time'        => $this->resource->closed_at,
            'is_pending'        => !$this->resource->is_approved,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getPollExtra(),
        ];
    }
}
