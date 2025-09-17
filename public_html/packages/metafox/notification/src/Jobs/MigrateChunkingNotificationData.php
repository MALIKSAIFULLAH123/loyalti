<?php

namespace MetaFox\Notification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Notification\Models\Notification;
use MetaFox\Notification\Models\Type;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingNotificationData extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $notificationIds = [])
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if (!count($this->notificationIds)) {
            return;
        }

        $notifications = Notification::query()
            ->whereIn('id', $this->notificationIds)
            ->get();

        if (!$notifications->count()) {
            return;
        }

        $notificationData = [];

        foreach ($notifications as $notification) {
            if (!$notification instanceof Notification) {
                continue;
            }

            $notificationType = Type::query()
                ->where('type', $notification->type)
                ->first();
            if (!$notificationType instanceof Type) {
                continue;
            }

            $itemModelClass = Relation::getMorphedModel($notification->itemType());
            if (!$itemModelClass || !class_exists($itemModelClass)) {
                continue;
            }

            $item = $itemModelClass::query()
                ->where('id', $notification->itemId())
                ->first();
            if (!$item instanceof $itemModelClass) {
                continue;
            }

            $notifiableModelClass = Relation::getMorphedModel($notification->notifiable_type);
            if (!$notifiableModelClass || !class_exists($notifiableModelClass)) {
                continue;
            }

            $notifiableItem = $notifiableModelClass::query()
                ->where('id', $notification->notifiable_id)
                ->first();
            if (!$notifiableItem instanceof IsNotifiable) {
                continue;
            }

            $handlerModelClass   = $notificationType->handler;
            $notificationHandler = new $handlerModelClass($item);
            if (!method_exists($notificationHandler, 'toArray')) {
                continue;
            }
            if ($notification->data && method_exists($notificationHandler, 'setData')) {
                $notificationHandler->setData($notification->data);
            }

            $oldNotification = $notification->toArray();
            unset($oldNotification['is_notified']);
            unset($oldNotification['is_read']);

            $notificationData[] = array_merge(
                $oldNotification,
                [
                    'is_request' => $notificationType->is_request,
                    'data'       => json_encode($notificationHandler->toArray($notifiableItem)),
                ]
            );
        }

        Notification::query()->upsert($notificationData, ['id']);
    }
}
