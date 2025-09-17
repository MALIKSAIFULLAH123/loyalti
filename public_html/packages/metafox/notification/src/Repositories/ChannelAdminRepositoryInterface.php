<?php

namespace MetaFox\Notification\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Notification\Models\NotificationChannel;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface NotificationChannel.
 * @mixin BaseRepository
 */
interface ChannelAdminRepositoryInterface
{
    /**
     * @param array $attributes
     * @return Builder
     */
    public function viewChannels(array $attributes): Builder;

    /**
     * @param int $id
     * @return NotificationChannel
     */
    public function toggleActive(int $id): NotificationChannel;
}
