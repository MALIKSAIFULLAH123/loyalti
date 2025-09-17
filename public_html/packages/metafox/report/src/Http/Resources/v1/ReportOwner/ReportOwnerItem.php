<?php

namespace MetaFox\Report\Http\Resources\v1\ReportOwner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Report\Models\ReportOwner as Model;
use MetaFox\Report\Models\ReportOwnerUser;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

/**
 * Class ReportOwnerItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ReportOwnerItem extends JsonResource
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
        /** @var ReportOwnerUser $userReport */
        $userReport = $this->resource->userReports()->with(['userEntity'])->orderByDesc('updated_at')->first();
        $userEntity = $userReport?->userEntity;
        $reasons    = [];
        if ($this->resource->total_report == 1) {
            $reasons = [
                'name'     => $userReport->reason?->name,
                'feedback' => $userReport->feedback,
            ];
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'report',
            'resource_name'     => $this->resource->entityType(),
            'embed_object'      => ResourceGate::asEmbed($this->resource->item),
            'last_user'         => new UserEntityDetail($userEntity),
            'total_report'      => $this->resource->total_report,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'reason'            => $reasons,
        ];
    }
}
