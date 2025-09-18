<?php

namespace MetaFox\LiveStreaming\Repositories\Eloquent;

use MetaFox\LiveStreaming\Models\NotificationSetting as Model;
use MetaFox\LiveStreaming\Repositories\NotificationSettingRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class NotificationSettingRepository.
 */
class NotificationSettingRepository extends AbstractRepository implements NotificationSettingRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    public function isTurnOffNotify(?User $owner, User $user): bool
    {
        if (!$owner) {
            return false;
        }

        $model = $this->getModel()->newQuery()
            ->where([
                'owner_id' => $owner->entityId(),
                'user_id'  => $user->entityId(),
            ])
            ->first();

        if ($model) {
            return true;
        }

        return false;
    }

    public function disabledNotification(User $owner, User $user): bool|Model
    {
        if ($this->isTurnOffNotify($owner, $user)) {
            return false;
        }

        $attributes = [
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ];
        $model = $this->getModel()->newModelInstance();
        $model->fill($attributes);
        $model->save();

        $model->refresh();

        return $model;
    }

    public function enabledNotification(User $owner, User $user): bool
    {
        if (!$this->isTurnOffNotify($owner, $user)) {
            return false;
        }

        $this->getModel()->newQuery()
            ->where([
                'owner_id' => $owner->entityId(),
                'user_id'  => $user->entityId(),
            ])
            ->delete();

        return true;
    }

    public function getDisabledUserIds($owner): array
    {
        $model = $this->getModel()->newQuery()
            ->where([
                'owner_id' => $owner->entityId(),
            ])
            ->get(['user_id']);

        return $model->pluck('user_id')->toArray();
    }
}
