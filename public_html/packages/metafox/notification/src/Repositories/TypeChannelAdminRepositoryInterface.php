<?php

namespace MetaFox\Notification\Repositories;

use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Models\TypeChannel;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface TypeChannelAdminRepositoryInterface.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface TypeChannelAdminRepositoryInterface
{
    /**
     * @param  string      $channel
     * @return TypeChannel
     */
    public function toggleChannelForType(Type $type, string $channel, int $isActive): TypeChannel;
}
