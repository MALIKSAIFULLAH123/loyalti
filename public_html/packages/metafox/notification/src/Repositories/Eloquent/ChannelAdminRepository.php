<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Notification\Models\NotificationChannel;
use MetaFox\Notification\Repositories\ChannelAdminRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class NotificationChannelRepositor.
 * @ignore
 * @codeCoverageIgnore
 *
 * @method NotificationChannel find($id, $columns = ['*'])
 * @method NotificationChannel getModel()
 */
class ChannelAdminRepository extends AbstractRepository implements ChannelAdminRepositoryInterface
{
    public function model()
    {
        return NotificationChannel::class;
    }

    /**
     * @inheritDoc
     */
    public function viewChannels(array $attributes): Builder
    {
        $query = $this->getModel()->newQuery();

        return $query->orderBy('id');
    }

    /**
     * @inheritDoc
     */
    public function toggleActive(int $id): NotificationChannel
    {
        $model = $this->find($id);

        $model->update([
            'is_active' => $model->is_active ? 0 : 1,
        ]);

        return $model;
    }
}
