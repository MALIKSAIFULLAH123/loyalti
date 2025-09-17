<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\Page as Model;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class PageSuggestion.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageSuggestion extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context      = user();
        $avatarExists = !empty($this->resource->avatar);

        return [
            'id'             => $this->resource->entityId(),
            'module_name'    => $this->resource->entityType(),
            'resource_name'  => $this->resource->entityType(),
            'title'          => ban_word()->clean($this->resource->name),
            'avatar'         => $avatarExists ? $this->resource->avatars : null,
            'short_name'     => UserFacade::getShortName($this->resource->name),
            'link'           => $this->resource->toLink(),
            'url'            => $this->resource->toUrl(),
            'is_liked'       => $this->resource->isMember($context),
            'router'         => $this->resource->toRouter(),
            'privacy_detail' => $this->getPrivacyDetail(),
        ];
    }

    protected function getPrivacyDetail(): ?array
    {
        return app('events')->dispatch(
            'activity.get_privacy_detail_on_owner',
            [user(), $this->resource],
            true
        );
    }
}
