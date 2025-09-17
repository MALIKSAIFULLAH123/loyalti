<?php

namespace MetaFox\Notification\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface TypeChannel.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface TypeChannelRepositoryInterface
{
    /**
     * @param  string     $channel
     * @return Collection
     */
    public function getTypesByChannel(string $channel = 'mail'): Collection;

    /**
     * @return array<mixed>
     */
    public function getChannelsForAllTypes(): array;

    /**
     * @return array<string>
     */
    public function getActiveChannels(): array;
}
