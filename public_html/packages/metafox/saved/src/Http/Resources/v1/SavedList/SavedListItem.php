<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\Saved\Models\SavedList as Model;
use MetaFox\User\Models\UserEntity;
use MetaFox\Platform\ResourcePermission as ACL;

/**
 * Class SavedListItem.
 * @property Model $resource
 */
class SavedListItem extends JsonResource
{
    //    use HasExtra;
    use HasStatistic;

    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_saved_item' => $this->resource->savedItems->count(),
            'total_member'     => $this->resource->savedListMembers->count(),
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $image      = null;
        $savedThumb = $this->resource->savedThumb;

        if (null != $savedThumb) {
            $item = $savedThumb->item;

            if ($item instanceof HasSavedItem) {
                $savedData = $item->toSavedItem();
                if (!empty($savedData)) {
                    if (!empty($savedData['image'])) {
                        $image = $savedData['image'];
                    }

                    if (empty($image) && !empty($savedData['user'])) {
                        /** @var UserEntity $userEntity */
                        $userEntity = $savedData['user'];
                        $image      = $userEntity->avatars;
                    }
                }
            }
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'saved',
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'image'             => $image,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'statistic'         => $this->getStatistic(),
            'extra'             => $this->getExtra(),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'privacy'           => $this->resource->privacy,
            'url'               => $this->resource->toUrl(),
            'link'              => $this->resource->toLink(),
        ];
    }

    protected function getExtra(): array
    {
        $context = user();

        return [
            'is_owner'       => $this->resource->userId() == $context->entityId(),
            ACL::CAN_EDIT    => $context->can('update', [$this->resource, $this->resource]),
            ACL::CAN_DELETE  => $context->can('delete', [$this->resource, $this->resource]),
            'can_add_friend' => $context->can('addFriend', [$this->resource, $this->resource]),
        ];
    }
}
