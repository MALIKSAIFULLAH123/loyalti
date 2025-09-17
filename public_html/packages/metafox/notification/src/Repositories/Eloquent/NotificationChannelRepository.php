<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Collection;
use MetaFox\Notification\Models\NotificationChannel;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class NotificationChannelRepositor.
 * @ignore
 * @codeCoverageIgnore
 */
class NotificationChannelRepository extends AbstractRepository implements NotificationChannelRepositoryInterface
{
    public function model()
    {
        return NotificationChannel::class;
    }

    public function extendChannel(string $channel, \Closure $callback): void
    {
        $this->getManager()->extend($channel, $callback);
    }

    private function getManager(): ChannelManager
    {
        return resolve(ChannelManager::class);
    }

    public function getActiveChannels(): Collection
    {
        return $this->getModel()->newQuery()
            ->where('is_active', '=', 1)
            ->orderBy('id')
            ->get();
    }

    public function getActiveChannelNames(): array
    {
        return $this->getActiveChannels()
            ->map(function (NotificationChannel $channel) {
                return $channel->name;
            })
            ->toArray();
    }

    public function getAllChannelNames(): array
    {
        return $this->getModel()->newQuery()
            ->orderBy('id')
            ->pluck('name')
            ->toArray();
    }
}
