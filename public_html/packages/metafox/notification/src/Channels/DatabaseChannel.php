<?php

namespace MetaFox\Notification\Channels;

use Illuminate\Support\Arr;
use MetaFox\Notification\Models\Notification as Model;
use MetaFox\Notification\Support\TypeManager;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Notifications\Notification;
use RuntimeException;

class DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return Model
     */
    public function send(IsNotifiable $notifiable, Notification $notification): Model
    {
        $repository = $notifiable->routeNotificationFor('database', $notification);

        return $repository->create(
            $this->buildPayload($notifiable, $notification)
        );
    }

    /**
     * Get the data for the notification.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return array<mixed>
     * @throws RuntimeException
     */
    protected function getData(IsNotifiable $notifiable, Notification $notification): array
    {
        if (method_exists($notification, 'toDatabase')) {
            return is_array($data = $notification->toDatabase($notifiable))
                ? $data : $data->data;
        }

        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }

        throw new RuntimeException('Notification is missing toDatabase / toArray method.');
    }

    /**
     * Send the given notification.
     *
     * @param IsNotifiable $notifiable
     * @param Notification $notification
     *
     * @return array<string, mixed>
     */
    public function buildPayload(IsNotifiable $notifiable, Notification $notification): array
    {
        $data = $notification->toArray($notifiable);

        return array_merge([
            'notifiable_id'   => $notifiable->entityId(),
            'notifiable_type' => $notifiable->entityType(),
            'type'            => $notification->getType(),
            'data'            => $data,
            'item_id'         => $data['item_id'],
            'item_type'       => $data['item_type'],
            'user_id'         => $data['user_id'],
            'user_type'       => $data['user_type'],
            'updated_at'      => now(),
        ], $this->handleDataItem($data));
    }

    private function handleDataItem($data): array
    {
        $dataItemId   = Arr::get($data['data'], 'item_id');
        $dataItemType = Arr::get($data['data'], 'item_type');
        if (!$dataItemId) {
            $dataItemId = $data['item_id'];
        }

        if (!$dataItemType) {
            $dataItemType = $data['item_type'];
        }

        return resolve(TypeManager::class)->transformDataItem($dataItemType, $dataItemId);
    }

    public function configMethodsCallbackMessage(): array
    {
        return [
            'callbackMessage',
        ];
    }

    public function validateConfiguration(): bool
    {
        return true;
    }
}
