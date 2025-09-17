<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\ActivityPoint\Models\PointTransaction as Model;
use MetaFox\ActivityPoint\Support\ActivityPoint;
use MetaFox\App\Models\Package;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PointTransactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PointTransactionItem extends JsonResource
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
        $user    = $owner = null;
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
        $color = ActivityPoint::ADDED_COLOR;

        if ($this->resource->is_subtracted) {
            $color = ActivityPoint::SUBTRACTED_COLOR;
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'activitypoint',
            'resource_name'     => $this->resource->entityType(),
            'package_id'        => $this->resource->package_id,
            'package_name'      => $this->getPackageName($this->resource->package_id),
            'user'              => $user,
            'user_id'           => $this->resource->user_id,
            'user_link'         => $this->resource->userEntity?->toUrl(),
            'owner'             => $owner,
            'type_id'           => $this->resource->type,
            'type_name'         => $this->getTypeName($this->resource->type),
            'action'            => $this->resource->getAction($context),
            'action_tooltip'    => strip_tag_content($this->resource->getAction($context)),
            'action_type'       => strip_tag_content($this->resource->getActionType($context)),
            'points'            => number_format($this->resource->points),
            'user_full_name'    => $userFullName,
            'owner_full_name'   => $ownerFullName,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'sx'                => [
                'points' => [
                    'color' => $color,
                ],
            ],
        ];
    }

    protected function getPackageName(string $packageId): string
    {
        $module = resolve('core.packages')->getPackageByName($packageId);
        if (!$module instanceof Package) {
            return __p('core::phrase.system');
        }

        return $module->title;
    }

    protected function getTypeName(int $type): string
    {
        $types = ActivityPoint::ALLOW_TYPES;

        foreach ($types as $label => $value) {
            if ($value == $type) {
                return __p($label);
            }
        }

        return MetaFoxConstant::EMPTY_STRING;
    }
}
