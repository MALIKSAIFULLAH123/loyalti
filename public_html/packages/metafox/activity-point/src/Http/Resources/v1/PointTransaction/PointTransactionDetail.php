<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ActivityPoint\Models\PointTransaction as Model;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class PointTransactionDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class PointTransactionDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $user = $owner = null;
        $context = user();
        $ownerFullName = $userFullName = __p('activitypoint::phrase.deleted_user');

        if (null !== $this->resource->userEntity) {
            $user = ResourceGate::detail($this->resource->userEntity);

            $userFullName = $this->resource->userEntity->name;
        }

        if (null !== $this->resource->ownerEntity) {
            $owner = ResourceGate::detail($this->resource->ownerEntity);

            $ownerFullName = $this->resource->ownerEntity->name;
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'activitypoint',
            'resource_name'     => $this->resource->entityType(),
            'app_id'            => $this->resource->module_id,
            'package'           => $this->resource->package_id,
            'user'              => $user,
            'owner'             => $owner,
            'user_full_name'    => $userFullName,
            'owner_full_name'   => $ownerFullName,
            'type'              => $this->resource->type,
            'action'            => $this->resource->getAction($context),
            'points'            => $this->resource->points,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->created_at,
        ];
    }
}
