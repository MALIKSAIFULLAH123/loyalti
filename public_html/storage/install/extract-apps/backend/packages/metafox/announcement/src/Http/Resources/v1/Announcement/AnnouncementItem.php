<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Announcement\Repositories\AnnouncementViewRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;

/**
 * Class AnnouncementItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AnnouncementItem extends JsonResource
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
        $context  = user();
        $roleText = __p('announcement::phrase.all_roles');
        $roles    = collect($this->resource->roles);
        if ($roles->isNotEmpty()) {
            $roleText = $roles->pluck('name')->implode(', ');
        }

        $styleName  = $this->resource->style?->name;
        $styleLabel = $this->resource->style?->label;

        return [
            'id'              => $this->resource->entityId(),
            'module_name'     => $this->resource->entityType(),
            'resource_name'   => $this->resource->entityType(),
            'title'           => $this->resource->title,
            'description'     => $this->resource->intro,
            'style'           => $this->when($styleName, $styleName),
            'style_label'     => $this->when($styleName, $styleLabel),
            'roles'           => $roleText,
            'icon_image'      => $this->resource->style?->icon_image,
            'icon_font'       => $this->resource->style?->icon_font,
            'start_date'      => Carbon::make($this->resource->start_date)?->toISOString(),
            'creation_date'   => $this->resource->created_at,
            'moderation_date' => $this->resource->updated_at,
            'is_active'       => $this->resource->is_active,
            'is_read'         => $this->isRead($context),
            'can_be_closed'   => $this->resource->can_be_closed,
            'link'            => $this->resource->toLink(),
            'url'             => $this->resource->toUrl(),
            'statistic'       => $this->getStatistic(),
            'extra'           => $this->getExtra(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getStatistic(): array
    {
        return [
            'total_comment' => $this->resource->total_comment,
            'total_reply'   => $this->resource->total_reply,
            'total_view'    => $this->resource->total_view,
            'total_like'    => $this->resource->total_like,
        ];
    }

    /**
     * @return array<string, int>
     * @throws AuthenticationException
     */
    public function getExtra(): array
    {
        /** @var \MetaFox\Announcement\Policies\AnnouncementPolicy $policy */
        $policy = PolicyGate::getPolicyFor(Model::class);

        return [
            'can_close' => $policy->close(user(), $this->resource),
        ];
    }

    protected function isRead(User $context): bool
    {
        return resolve(AnnouncementViewRepositoryInterface::class)
            ->checkViewAnnouncement($context->entityId(), $this->resource->entityId());
    }
}
