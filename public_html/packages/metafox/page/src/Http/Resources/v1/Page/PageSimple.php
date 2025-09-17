<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\Page as Model;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class PagePreview.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageSimple extends JsonResource
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
        $context      = user();
        $avatarExists = !empty($this->resource->avatar);

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => ban_word()->clean($this->resource->name),
            'avatar'        => $avatarExists ? $this->resource->avatars : null,
            'short_name'    => UserFacade::getShortName(ban_word()->clean($this->resource->name)),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'is_liked'      => $this->resource->isMember($context),
            'router'        => $this->resource->toRouter(),
        ];
    }
}
