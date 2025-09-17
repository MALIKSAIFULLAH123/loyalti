<?php

namespace MetaFox\Notification\Repositories;

use Illuminate\Support\Collection;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface NotificationChannel.
 * @mixin BaseRepository
 */
interface NotificationChannelRepositoryInterface
{
    /**
     * @return Collection
     */
    public function getActiveChannels(): Collection;

    /**
     * @return array
     */
    public function getActiveChannelNames(): array;

    public function getAllChannelNames(): array;
}
