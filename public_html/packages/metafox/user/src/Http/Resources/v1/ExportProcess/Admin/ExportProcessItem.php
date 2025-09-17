<?php

namespace MetaFox\User\Http\Resources\v1\ExportProcess\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\ExportProcess as Model;
use MetaFox\User\Support\User as UserSupport;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ExportProcessItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ExportProcessItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $statusOptions = Arr::mapWithKeys(UserSupport::allowedStatusExportOptions(), function ($item) {
            return [$item['value'] => $item['label']];
        });

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'user',
            'resource_name' => $this->resource->entityType(),
            'status'        => Arr::get($statusOptions, $this->resource->status, $this->resource->status),
            'filename'      => $this->resource->filename,
            'creation_date' => $this->resource->created_at,
            'total_user'    => $this->resource->total_user,
            'user'          => $this->userResource($this->resource->user),
            'extra'         => [
                'can_download' => $this->resource->status === UserSupport::EXPORT_STATUS_COMPLETED,
            ],
        ];
    }

    protected function userResource(mixed $user): ?JsonResource
    {
        if (!$user instanceof User) {
            return null;
        }

        $isDeleted = $user->isDeleted();
        return new JsonResource([
            'display_name' => $this->when($isDeleted, __p('core::phrase.deleted_user'), $user->display_name),
            'created_at'   => $user->created_at,
            'url'          => $this->when($isDeleted, null, $user->toUrl()),
        ]);
    }
}
