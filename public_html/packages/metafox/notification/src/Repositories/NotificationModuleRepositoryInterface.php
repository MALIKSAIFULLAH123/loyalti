<?php

namespace MetaFox\Notification\Repositories;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Notification\Models\NotificationModule;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface NotificationModule.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface NotificationModuleRepositoryInterface
{
    /**
     * @param string $channel
     *
     * @return Collection
     */
    public function getModulesByChannel(string $channel = 'mail'): Collection;

    /**
     * @param array $attributes
     *
     * @return Collection
     */
    public function viewModules(array $attributes): Collection;

    /**
     * @param string $module
     * @param string $channel
     * @param int    $active
     *
     * @return NotificationModule
     */
    public function toggleChannel(string $module, string $channel, int $active): NotificationModule;
}
