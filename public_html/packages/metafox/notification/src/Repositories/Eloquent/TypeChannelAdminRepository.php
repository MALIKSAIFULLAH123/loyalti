<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Models\TypeChannel;
use MetaFox\Notification\Repositories\TypeChannelAdminRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class TypeChannelAdminRepository.
 ** @method TypeChannel getModel()
 * @method TypeChannel find($id, $columns = ['*'])()
 */
class TypeChannelAdminRepository extends AbstractRepository implements TypeChannelAdminRepositoryInterface
{
    public function model()
    {
        return TypeChannel::class;
    }

    public function toggleChannelForType(Type $type, string $channel, int $isActive): TypeChannel
    {
        return $this->updateOrCreate([
            'type_id' => $type->entityId(),
            'channel' => $channel,
        ], [
            'is_active' => $isActive,
        ]);
    }
}
