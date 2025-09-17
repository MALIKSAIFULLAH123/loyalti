<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Report\Models\ReportItem as Model;
use MetaFox\Platform\Contracts\User;

/**
 * Class ReportItemItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportItemItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $username = __p('core::phrase.deleted_user');
        $user     = $this->resource->user;
        if ($user instanceof User) {
            $username = $user->display_name;
        }

        $reason     = $this->resource->reason;
        $reasonText =  $reason?->name ?: __p('report::phrase.deleted_reason');

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'report',
            'resource_name'     => $this->resource->entityType(),
            'user'              => $user,
            'user_name'         => $username,
            'user_url'          => $user?->toUrl(),
            'reason'            => $reason,
            'reason_text'       => $reasonText,
            'feedback'          => $this->resource->feedback,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
