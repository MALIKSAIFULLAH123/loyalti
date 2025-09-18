<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Core\Models\SiteSetting;
use MetaFox\Group\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

class ModelUpdatedListener
{
    public function __construct(
        protected UserPrivacyRepositoryInterface $repository,
        protected AnnouncementRepositoryInterface $announcementRepository
    ) {
    }

    public function handle(Model $model): void
    {
        $this->handlePrivacyResource($model);

        if (!$model instanceof Content) {
            return;
        }

        if ($model->entityType() == 'feed') {
            $this->handleAnnouncement($model);
        }
    }

    /**
     * @param Content $model
     */
    public function handleAnnouncement(Content $model): void
    {
        if ($model->status == MetaFoxConstant::ITEM_STATUS_REMOVED) {
            $this->announcementRepository->deleteByItem($model->entityId(), $model->entityType());
        }
    }

    /**
     * @param Model $model
     */
    public function handlePrivacyResource(Model $model): void
    {
        $setting = 'group.default_item_privacy';

        if (!$model instanceof SiteSetting) {
            return;
        }

        if ($model->name != $setting) {
            return;
        }

        $value = $model->value_actual ?? $model->value_default;
        $this->repository->updatePrivacyResourceValueByEntity('group', $value);
    }
}
