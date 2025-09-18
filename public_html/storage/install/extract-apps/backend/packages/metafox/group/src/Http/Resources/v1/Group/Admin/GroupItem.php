<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Support\Browse\Traits\Group\StatisticTrait;
use MetaFox\Group\Support\GroupRole;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\User\Support\Facades\User;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class GroupItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class GroupItem extends JsonResource
{
    use StatisticTrait;
    use HasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $shortDescription = MetaFoxConstant::EMPTY_STRING;

        $groupText = $this->resource->groupText;
        if ($groupText) {
            $shortDescription = parse_output()->getDescription($groupText->text_parsed);
        }
        $extra = $this->getGroupExtra();

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->name,
            'description'   => $shortDescription,
            'reg_method'    => $this->resource->privacy_type,
            'reg_name'      => __p(PrivacyTypeHandler::PRIVACY_PHRASE[$this->resource->privacy_type]),
            'image'         => [
                'url'       => $this->resource->cover,
                'file_type' => 'image/*',
            ],
            'is_approved'   => $this->resource->is_approved,
            'is_featured'   => $this->resource->is_featured,
            'is_sponsored'  => $this->resource->is_sponsor,
            'short_name'    => User::getShortName(ban_word()->clean($this->resource->name)),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'creation_date' => $this->resource->created_at,
            'statistic'     => $this->getStatistic(),
            'extra'         => $extra,
        ];
    }

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    public function getGroupExtra(): array
    {
        $extra = $this->getExtra();

        $customExtra = GroupRole::getGroupSettingPermission(user(), $this->resource);

        return array_merge($extra, $customExtra);
    }
}
