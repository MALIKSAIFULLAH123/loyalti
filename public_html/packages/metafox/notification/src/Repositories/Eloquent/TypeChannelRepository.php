<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Notification\Models\Type;
use MetaFox\Notification\Models\TypeChannel;
use MetaFox\Notification\Repositories\TypeChannelRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class TypeChannelRepository.
 ** @method TypeChannel getModel()
 * @method TypeChannel find($id, $columns = ['*'])()
 */
class TypeChannelRepository extends AbstractRepository implements TypeChannelRepositoryInterface
{
    public function model()
    {
        return TypeChannel::class;
    }

    /**
     * @param string $channel
     * @return Collection
     */
    public function getTypesByChannel(string $channel = 'mail'): Collection
    {
        $table = $this->model->getTable();

        $query = $this->getModel()->newQuery()
            ->where("$table.is_active", Type::IS_ACTIVE)
            ->with(['type'])
            ->join('notification_types', 'notification_types.id', '=', "$table.type_id")
            ->where("$table.channel", $channel);

        $query->addScope(resolve(PackageScope::class, [
            'table' => 'notification_types',
        ]));

        return $query->select("$table.*")
            ->orderBy("$table.ordering")
            ->get();
    }

    public function getChannelsForAllTypes(): array
    {
        $typeChannels = [];
        $query        = TypeChannel::query()
            ->from('notification_types', 'types')
            ->select([
                'types.type', 'type_channels.channel',
            ])
            ->join('notification_type_channels as type_channels', function (JoinClause $join) {
                $join->on('types.id', '=', 'type_channels.type_id');
                $join->where('type_channels.is_active', 1);
            })
            ->join('notification_channels as channels', function (JoinClause $join) {
                $join->on('channels.name', '=', 'type_channels.channel');
                $join->where('channels.is_active', 1);
            });

        foreach ($query->cursor() as $item) {
            $typeChannels[$item->type][] = $item->channel;
        }

        return $typeChannels;
    }

    public function getActiveChannels(): array
    {
        return TypeChannel::query()
            ->select(['channel'])
            ->where('is_active', 1)
            ->groupBy('channel')
            ->pluck('channel')
            ->toArray();
    }
}
