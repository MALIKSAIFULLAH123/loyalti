<?php

namespace MetaFox\Notification\Repositories;

use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SettingRepositoryInterface.
 *
 * @mixin BaseRepository
 */
interface SettingRepositoryInterface
{
    /**
     * @param IsNotifiable $notifiable
     *
     * @return array<mixed>
     */
    public function getChannelsForNotifiable(IsNotifiable $notifiable): array;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return bool
     */
    public function updateByChannel(User $context, array $attributes): bool;

    /**
     * @param User $context
     *
     * @return array
     */
    public function getChannelsSubscribed(User $context): array;
}
