<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Support\ResourcePermission;

/**
 * Class QuizItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class QuizItem extends JsonResource
{
    use HasExtra;
    use HasFeedParam;

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
        $shortDescription = $text = '';
        $quizText         = $this->resource->quizText;
        if ($quizText) {
            $shortDescription = parse_output()->getDescription($quizText->text_parsed);
            $text             = parse_output()->parseItemDescription($quizText->text_parsed);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->title,
            'description'       => $shortDescription,
            'is_sponsored'      => (bool) $this->resource->is_sponsor,
            'is_featured'       => (bool) $this->resource->is_featured,
            'is_approved'       => (bool) $this->resource->is_approved,
            'text'              => $text,
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'is_sponsored_feed' => (bool) $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getCustomExtra(),
        ];
    }

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getCustomExtra(): array
    {
        $extras = $this->getExtra();

        $context = user();

        return array_merge($extras, [
            ResourcePermission::CAN_PLAY => $context->can('play', [Model::class, $this->resource]),
        ]);
    }
}
