<?php

namespace MetaFox\Photo\Observers;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\CollectionStatistic;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;

/**
 * Class PhotoGroupObserver.
 */
class PhotoGroupObserver
{
    public function created(PhotoGroup $model): void
    {
        $params = [
            'item_type' => $model->entityType(),
            'item_id'   => $model->entityId(),
        ];

        CollectionStatistic::query()->firstOrCreate($params, $params);
    }

    public function updated(PhotoGroup $model): void
    {
        if ($model->wasChanged('privacy')) {
            $this->updatedPrivacyItem($model);
        }
    }

    protected function updatedPrivacyItem(PhotoGroup $model): void
    {
        $items = $model->items;
        foreach ($items as $item) {
            $detail = $item->detail;

            if (null === $detail) {
                continue;
            }

            if (!$detail instanceof HasPrivacy) {
                continue;
            }

            $this->updatePrivacy($detail, $model);
        }
    }

    public function deleted(PhotoGroup $photoGroup): void
    {
        app('events')->dispatch('notification.notification.delete_mass_notification_by_item', [$photoGroup], true);

        $this->deleteGroupItems($photoGroup);
    }

    private function updatePrivacy(HasPrivacy $detail, PhotoGroup $model): void
    {
        if ($detail->privacy != $model->privacy) {
            $detail->privacy = $model->privacy;
        }

        if ($detail instanceof AppendPrivacyList) {
            $detail->setPrivacyListAttribute($model->getPrivacyListAttribute());
        }

        if ($detail instanceof Model) {
            $detail->save();
        }
    }

    protected function deleteGroupItems(PhotoGroup $photoGroup): void
    {
        $photoGroup->items()->get()->collect()->each(function (mixed $groupItem) {
            if (!$groupItem instanceof PhotoGroupItem) {
                return true;
            }

            $media = $groupItem->detail()->first();
            if ($media instanceof Media) {
                $media->delete();
            }
        });
    }
}
