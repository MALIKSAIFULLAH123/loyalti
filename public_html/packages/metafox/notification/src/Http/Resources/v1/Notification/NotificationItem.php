<?php

namespace MetaFox\Notification\Http\Resources\v1\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Notification\Models\Notification as Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\Notify;
use MetaFox\Platform\Notifications\Notification;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

/**
 * Class NotificationItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class NotificationItem extends JsonResource
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
        $extraData = $this->resource->data;

        $handlerClass = Notify::getHandler($this->resource->type);

        $data = Arr::get($extraData, 'data');

        /** @var Notification|null $handler */
        $handler = null;

        $user = $this->resource->userEntity;
        if ($handlerClass) {
            //Handle in case notification for deleting item
            $handler = resolve($handlerClass);

            if ($this->resource->item instanceof Entity) {
                $handler->setModel($this->resource->item);
            }

            if (is_array($data) && count($data) > 0) {
                $handler->setData($data);
            }
            $handler->setUser($this->resource->userEntity);
        }
        if ($handler instanceof Notification) {
            $handler->setNotifiable($this->resource->notifiable);
        }

        return [
            'id'                => $this->resource->entityId(),
            'resource_name'     => $this->resource->entityType(),
            'message'           => $handler ? $handler->callbackMessage() : '',
            'type'              => $this->resource->type,
            'is_notified'       => $this->resource->is_notified,
            'is_read'           => $this->resource->is_read,
            'link'              => $handler ? $handler->toLink() : '',
            'weblink'           => $handler ? $handler->toUrl() : '',
            'router'            => $handler ? $handler->toRouter() : '',
            'user'              => new UserEntityDetail($user),
            'module_item'       => getAliasByEntityType($this->resource->item_type),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
