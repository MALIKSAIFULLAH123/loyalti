<?php

namespace MetaFox\Report\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Models\ReportItemAggregate;
use MetaFox\Report\Policies\ReportItemPolicy;
use MetaFox\Report\Repositories\ReportItemAdminRepositoryInterface;
use MetaFox\Report\Repositories\ReportItemAggregateAdminRepositoryInterface;

/**
 * Class ReportItemRepository.
 *
 * @method ReportItem getModel()
 * @method ReportItem find($id, $columns = ['*'])()
 */
class ReportItemAdminRepository extends AbstractRepository implements ReportItemAdminRepositoryInterface
{
    public function model(): string
    {
        return ReportItem::class;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function deleteReportByItem(User $context, string $itemType, int $itemId): bool
    {
        policy_authorize(ReportItemPolicy::class, 'delete', $context);

        $this->handleDeleteReportByItem($itemType, $itemId);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function viewReportItems(User $context, array $attributes = []): Paginator
    {
        $limit       = Arr::get($attributes, 'limit');
        $sort        = Arr::get($attributes, 'sort', Browse::SORT_RECENT);
        $sortType    = Arr::get($attributes, 'sort', Browse::SORT_TYPE_DESC);
        $aggregateId = Arr::get($attributes, 'aggregate_id', 0);

        $relations = ['user', 'item', 'reason'];
        $query     = $this->getModel()->newModelQuery();

        $aggregate = $this->getReportAgggregateAdminRepository()
            ->getModel()
            ->newModelQuery()
            ->find($aggregateId);
        if ($aggregate instanceof ReportItemAggregate) {
            $query = $query
                ->where('item_type', '=', $aggregate->itemType())
                ->where('item_id', '=', $aggregate->itemId());
        }

        $sortScope = new SortScope($sort, $sortType);

        return $query->with($relations)->addScope($sortScope)->paginate($limit);
    }

    public function getReportAgggregateAdminRepository(): ReportItemAggregateAdminRepositoryInterface
    {
        return resolve(ReportItemAggregateAdminRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function handleDeleteReportByItem(string $itemType, int $itemId): bool
    {
        $this->getModel()->newModelQuery()
            ->where('item_type', '=', $itemType)
            ->where('item_id', '=', $itemId)
            ->get()
            ->collect()
            ->each(function (ReportItem $item) {
                $item->delete();
            });

        return true;
    }
}
