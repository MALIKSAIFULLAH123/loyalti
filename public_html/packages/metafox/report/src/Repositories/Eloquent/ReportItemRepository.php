<?php

namespace MetaFox\Report\Repositories\Eloquent;

use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Policies\ReportItemPolicy;
use MetaFox\Report\Repositories\ReportItemRepositoryInterface;

/**
 * Class ReportItemRepository.
 * @method ReportItem getModel()
 * @method ReportItem find($id, $columns = ['*'])()
 */
class ReportItemRepository extends AbstractRepository implements ReportItemRepositoryInterface
{
    use CollectTotalItemStatTrait;

    public function model(): string
    {
        return ReportItem::class;
    }

    public function createReport(User $context, array $attributes)
    {
        policy_authorize(ReportItemPolicy::class, 'create', $context, $attributes);

        $report = new ReportItem(array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]));

        $report->save();

        return $report->refresh();
    }

    public function canReport(User $context, int $itemId, string $itemType): bool
    {
        $exists = $this->getModel()->newQuery()
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType())
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->exists();

        return $exists === false;
    }

    public function getReportItem(array $conditions)
    {
        return $this->getModel()->newQuery()
            ->where($conditions)
            ->first();
    }
}
