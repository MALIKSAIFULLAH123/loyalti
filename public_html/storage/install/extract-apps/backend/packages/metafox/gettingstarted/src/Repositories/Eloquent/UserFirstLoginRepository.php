<?php

namespace MetaFox\GettingStarted\Repositories\Eloquent;

use MetaFox\GettingStarted\Models\UserFirstLogin as Model;
use MetaFox\GettingStarted\Repositories\UserFirstLoginRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class UserFirstLoginRepository.
 * @property Model $model
 * @method   Model getModel()
 * @method   Model find($id, $columns = ['*'])()
 * @ignore
 * @codeCoverageIgnore
 */
class UserFirstLoginRepository extends AbstractRepository implements UserFirstLoginRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    public function initUserFirstLoginData(User $user): void
    {
        $resolutions = [MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE];
        foreach ($resolutions as $resolution) {
            $this->getModel()->newQuery()->create([
                'user_id'    => $user->entityId(),
                'user_type'  => $user->entityType(),
                'resolution' => $resolution,
            ]);
        }
    }

    public function deleteByUser(User $user): void
    {
        $this->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->where('user_type', $user->entityType())
            ->delete();
    }

    public function isFirstLogin(User $context, string $resolution): bool
    {
        return $this->getModel()
            ->newQuery()
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType())
            ->where('resolution', $resolution)
            ->exists();
    }

    public function markFirstLogin(User $context, string $resolution): void
    {
        $this->getModel()
            ->newQuery()
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType())
            ->where('resolution', $resolution)
            ->delete();
    }
}
