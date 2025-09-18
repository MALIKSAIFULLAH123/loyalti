<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Support\GroupRole;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class PageSuggestion.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GroupSuggestion extends JsonResource
{
    use HasExtra;

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
        $context = user();
        $extra   = $this->getGroupExtra();

        $privacyLabel = $extra['can_view_privacy']
            ? __p(PrivacyTypeHandler::PRIVACY_PHRASE[$this->resource->privacy_type])
            : null;

        return [
            'id'             => $this->resource->entityId(),
            'module_name'    => $this->resource->entityType(),
            'resource_name'  => $this->resource->entityType(),
            'title'          => ban_word()->clean($this->resource->name),
            'avatar'         => empty($this->resource->avatars) ? null : $this->resource->avatars,
            'cover'          => $this->resource->covers,
            'reg_method'     => $this->resource->privacy_type,
            'reg_name'       => $privacyLabel,
            'short_name'     => UserFacade::getShortName(ban_word()->clean($this->resource->name)),
            'link'           => $this->resource->toLink(),
            'url'            => $this->resource->toUrl(),
            'is_liked'       => $this->resource->isMember($context),
            'router'         => $this->resource->toRouter(),
            'privacy_detail' => $this->getPrivacyDetail(),
        ];
    }

    /**
     * @throws AuthenticationException
     */
    protected function getPrivacyDetail(): ?array
    {
        return app('events')->dispatch(
            'activity.get_privacy_detail_on_owner',
            [user(), $this->resource],
            true
        );
    }

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    public function getGroupExtra(): array
    {
        $extra = $this->getExtra();

        $customExtra = GroupRole::getGroupSettingPermission(user(), $this->resource->refresh());

        return array_merge($extra, $customExtra);
    }
}
