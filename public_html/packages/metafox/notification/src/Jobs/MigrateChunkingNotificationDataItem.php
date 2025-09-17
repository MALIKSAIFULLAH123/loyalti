<?php

namespace MetaFox\Notification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Notification\Models\Notification;
use MetaFox\Notification\Support\TypeManager;
use MetaFox\Platform\Jobs\AbstractJob;

class MigrateChunkingNotificationDataItem extends AbstractJob
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

        foreach ($notifications as $notification) {
            if (!$notification instanceof Notification) {
                continue;
            }

            $data         = $notification->data;
            $dataItemId   = Arr::get($data['data'], 'item_id');
            $dataItemType = Arr::get($data['data'], 'item_type');

            if (!$dataItemId) {
                $dataItemId = $data['item_id'];
            }

            if (!$dataItemType) {
                $dataItemType = $data['item_type'];
            }

            $dataItem = resolve(TypeManager::class)->transformDataItem($dataItemType, $dataItemId);

            $notification->update($dataItem);
        }
    }
}
