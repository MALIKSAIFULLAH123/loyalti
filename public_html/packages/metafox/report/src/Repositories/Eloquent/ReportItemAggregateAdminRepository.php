<?php

namespace MetaFox\Report\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Notifications\ProcessedReportItemNotification;
use MetaFox\Report\Repositories\ReportItemAggregateAdminRepositoryInterface;
use MetaFox\Report\Models\ReportItemAggregate as Model;
use MetaFox\Report\Policies\ReportItemPolicy;
use MetaFox\Report\Repositories\ReportItemAdminRepositoryInterface;

/**
 * Class ReportItemAggregateRepository.
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class ReportItemAggregateAdminRepository extends AbstractRepository implements ReportItemAggregateAdminRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    protected function getReportItemRepository(): ReportItemAdminRepositoryInterface
    {
        return resolve(ReportItemAdminRepositoryInterface::class);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewAggregations(User $context, array $attributes = []): Paginator
    {
        $itemType = Arr::get($attributes, 'item_type');
        $limit    = Arr::get($attributes, 'limit');
        $sort     = Arr::get($attributes, 'sort');
        $sortType = Arr::get($attributes, 'sort_type');

        $query     = $this->getModel()->newModelQuery();
        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        if ($itemType) {
            $query->where('item_type', $itemType);
        }

        return $query
            ->addScope($sortScope)
            ->whereHas('item')
            ->paginate($limit);
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteAggregation(User $context, int $id): bool
    {
        $aggregate = $this->with(['item'])->find($id);

        policy_authorize(ReportItemPolicy::class, 'delete', $context);

        $this->getReportItemRepository()->deleteReportByItem($context, $aggregate->item_type, $aggregate->item_id);

        return $aggregate->delete() ?? false;
    }

    /**
     * @inheritDoc
     */
    public function upsertAggregationByReport(ReportItem $reportItem): Model
    {
        /** @var Model $aggregate */
        $aggregate = $this->getModel()
            ->newModelQuery()
            ->firstOrCreate([
                'item_id'   => $reportItem->itemId(),
                'item_type' => $reportItem->itemType(),
            ], [
                'last_user_id'   => $reportItem->userId(),
                'last_user_type' => $reportItem->userType(),
            ]);

        $aggregate->fill([
            'last_user_id'   => $reportItem->userId(),
            'last_user_type' => $reportItem->userType(),
            'total_reports'  => ++$aggregate->total_reports,
        ]);

        $aggregate->save();

        return $aggregate;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function processAggregation(User $context, int $id): bool
    {
        $aggregate = $this->getModel()
            ->newModelQuery()
            ->where('id', '=', $id)
            ->with(['item'])
            ->first();

        if (!$aggregate instanceof Model) {
            return false;
        }

        if (!$aggregate->item instanceof Content) {
            // If no item exists then assume that it's already processed
            return true;
        }

        $reports = $this->getReportItemRepository()
            ->getModel()
            ->newModelQuery()
            ->where('item_id', '=', $aggregate->itemId())
            ->where('item_type', '=', $aggregate->itemType())
            ->with(['user'])
            ->get()
            ->collect();

        $users = $reports->pluck('user')->unique()->filter(function (mixed $user) {
            return $user instanceof IsNotifiable;
        })->values();

        Notification::send($users, new ProcessedReportItemNotification($aggregate->item));

        return true;
    }

    public function updateTotalReportsByReport(ReportItem $report): bool
    {
        $itemsCount = $this->getReportItemRepository()
            ->getModel()
            ->newModelQuery()
            ->where('item_type', $report->itemType())
            ->where('item_id', $report->itemId())
            ->count();

        $aggregate = $this->getModel()
            ->newModelQuery()
            ->where('item_type', $report->itemType())
            ->where('item_id', $report->itemId())
            ->first();

        if (!$aggregate instanceof Model) {
            return false;
        }

        if ($itemsCount > 0) {
            return $aggregate->update(['total_reports' => $itemsCount]);
        }

        return $aggregate->delete();
    }
}
