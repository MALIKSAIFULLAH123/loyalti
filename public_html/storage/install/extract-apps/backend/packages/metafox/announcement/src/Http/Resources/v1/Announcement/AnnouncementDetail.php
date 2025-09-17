<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Announcement\Models\Announcement as Model;
use MetaFox\Announcement\Repositories\AnnouncementViewRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Announcement\Http\Resources\v1\Traits\AnnouncementHasExtra;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/*
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
|
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
| @link /app/Console/Commands/stubs/module/resources/detail.stub
|
*/

/**
 * Class AnnouncementDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AnnouncementDetail extends JsonResource
{
    use AnnouncementHasExtra;
    use HasStatistic;
    use IsLikedTrait;
    use HasFeedParam;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $context = user();

        $roleText = __p('announcement::phrase.all_roles');
        $roles    = collect($this->resource->roles);
        if ($roles->isNotEmpty()) {
            $roleText = $roles->pluck('name')->implode(', ');
        }

        $content = $this->resource->content ?? $this->resource->masterContent;

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->title,
            'description'       => $this->resource->intro,
            'text'              => parse_output()->parseUrl($content?->text),
            'text_parsed'       => parse_output()->parseUrl($content?->text_parsed),
            'style'             => $this->resource->style?->name,
            'style_label'       => $this->resource->style?->label,
            'roles'             => $roleText,
            'icon_image'        => $this->resource->style?->icon_image,
            'icon_font'         => $this->resource->style?->icon_font,
            'can_be_closed'     => $this->resource->can_be_closed,
            'is_liked'          => $this->isLike($context, $this->resource),
            'feed_param'        => $this->getFeedParams(),
            'show_in_dashboard' => $this->resource->show_in_dashboard,
            'start_date'        => Carbon::make($this->resource->start_date)?->toISOString(),
            'creation_date'     => $this->resource->created_at,
            'moderation_date'   => $this->resource->updated_at,
            'is_active'         => $this->resource->is_active,
            'is_read'           => $this->isRead($context),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'privacy'           => MetaFoxPrivacy::EVERYONE,
            'extra'             => $this->getAnnouncementExtra(),
            'statistic'         => $this->getStatistic(),
        ];
    }

    protected function isRead(User $context): bool
    {
        return resolve(AnnouncementViewRepositoryInterface::class)
            ->checkViewAnnouncement($context->entityId(), $this->resource->entityId());
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_comment' => $this->resource->total_comment,
            'total_reply'   => $this->resource->total_reply,
            'total_view'    => $this->resource->total_view,
            'total_like'    => $this->resource->total_like,
        ];
    }
}
