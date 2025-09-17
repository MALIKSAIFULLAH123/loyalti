<?php

namespace MetaFox\Announcement\Http\Resources\v1\Announcement\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Announcement\Models\Announcement as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class AnnouncementItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class AnnouncementItem extends JsonResource
{
    public function toArray($request)
    {
        $roleText = __p('announcement::phrase.all_roles');
        $roles    = collect($this->resource->roles);
        if ($roles->isNotEmpty()) {
            $roleText = $roles->pluck('name')->implode(', ');
        }

        return [
            'id'              => $this->resource->entityId(),
            'module_name'     => $this->resource->entityType(),
            'resource_name'   => $this->resource->entityType(),
            'title'           => $this->resource->title,
            'description'     => $this->resource->intro,
            'style'           => $this->resource->style?->label ?? __p('announcement::phrase.style_none'),
            'roles'           => $roleText,
            'icon_image'      => $this->resource->style?->icon_image,
            'icon_font'       => $this->resource->style?->icon_font,
            'start_date'      => Carbon::make($this->resource->start_date)?->toISOString(),
            'creation_date'   => $this->resource->created_at,
            'moderation_date' => $this->resource->updated_at,
            'is_active'       => $this->resource->is_active,
            'can_be_closed'   => $this->resource->can_be_closed,
            'link'            => $this->resource->toLink(),
            'url'             => $this->resource->toUrl(),
            'links'           => [
                'editItem' => $this->resource->admin_edit_url,
            ],
        ];
    }
}
