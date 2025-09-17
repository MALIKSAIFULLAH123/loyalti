<?php

namespace MetaFox\Follow\Http\Resources\v1\Follow;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Http\Resources\v1\User\UserItem;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityPreview;
use MetaFox\User\Models\UserEntity as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * @property Model $resource
 *                           Class FollowItem.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class FollowItem extends JsonResource
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
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.14', '<')) {
            return $this->handleOldVersion($request);
        }

        return $this->handleNewVersion($request);
    }

    protected function handleOldVersion($request): array
    {
        return (new UserItem($this->resource))->toArray($request);
    }

    protected function handleNewVersion($request): array
    {
        $resource = (new UserEntityPreview($this->resource))->toArray($request);

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'follow',
            'resource_name' => 'follow',
            'item'          => $resource,
        ];
    }
}
