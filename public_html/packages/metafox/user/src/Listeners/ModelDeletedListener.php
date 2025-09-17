<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\HasShortcutItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Models\UserRelationHistory;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Repositories\UserShortcutRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

class ModelDeletedListener
{
    public function userPrivacyRepository()
    {
        return resolve(UserPrivacyRepositoryInterface::class);
    }

    public function handle($model)
    {
        if ($model instanceof User) {
            UserEntity::deleteEntity($model->entityId());

            if ($model->entityType() == \MetaFox\User\Models\User::ENTITY_TYPE) {
                $this->userPrivacyRepository()->deleteUserPrivacy($model->entityId());
            }
            $this->handleShortcutByItem($model);
            $this->handleRelationHistory($model);
            $this->handleRelationWith($model);
        }
        $this->handleShortcut($model);
    }

    protected function handleShortcut($model)
    {
        if ($model instanceof HasShortcutItem) {
            resolve(UserShortcutRepositoryInterface::class)->deletedBy($model);
        }
    }

    protected function handleShortcutByItem($model)
    {
        resolve(UserShortcutRepositoryInterface::class)->deletedByItem($model);
    }

    protected function handleRelationHistory(User $model)
    {
        if (!$model instanceof UserModel) {
            return;
        }

        $query = UserRelationHistory::query()->where('user_id', $model->entityId());

        foreach ($query->cursor() as $history) {
            if (!$history instanceof UserRelationHistory) {
                continue;
            }

            $feed = $history->activity_feed()->first();
            if ($feed) {
                $feed->delete();
            }

            $history->delete();
        }
    }

    protected function handleRelationWith(User $model): void
    {
        UserProfile::query()
            ->where('relation_with', $model->entityId())
            ->update(['relation_with' => 0]);
    }
}
