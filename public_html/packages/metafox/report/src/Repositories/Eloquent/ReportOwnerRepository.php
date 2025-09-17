<?php

namespace MetaFox\Report\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Report\Models\ReportOwner;
use MetaFox\Report\Repositories\ReportOwnerRepositoryInterface;
use MetaFox\Report\Repositories\ReportOwnerUserRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * @method ReportOwner find($id, $columns = ['*'])
 * @method ReportOwner getModel()
 */
class ReportOwnerRepository extends AbstractRepository implements ReportOwnerRepositoryInterface
{
    public function model(): string
    {
        return ReportOwner::class;
    }

    /**
     * @return ReportOwnerUserRepositoryInterface
     */
    private function reportOwnerUserRepository(): ReportOwnerUserRepositoryInterface
    {
        return resolve(ReportOwnerUserRepositoryInterface::class);
    }

    public function viewReports(User $context, array $attributes): Paginator
    {
        $ownerId  = $attributes['owner_id'];
        $limit    = $attributes['limit'];
        $sortType = $attributes['sort_type'];

        $owner = UserEntity::getById($ownerId)->detail;
        gate_authorize($context, 'viewReportContent', $owner, $owner);

        return $this->getModel()->newQuery()
            ->where('owner_id', $ownerId)
            ->where('total_report', '>', 0)
            ->orderBy('updated_at', $sortType)
            ->simplePaginate($limit);
    }

    public function createReportOwner(User $context, array $attributes)
    {
        $reportData = [
            'item_id'   => $attributes['item_id'],
            'item_type' => $attributes['item_type'],
        ];

        $report = new ReportOwner($reportData);
        $item   = $report->item;
        if (null == $item) {
            throw (new ModelNotFoundException())->setModel($attributes['item_type']);
        }

        $owner = $item->owner;
        gate_authorize($context, 'reportToOwner', $item, $item);

        $reportData = array_merge($reportData, [
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ]);

        /** @var ReportOwner $report */
        $report = $this->getModel()->newQuery()
            ->firstOrCreate($reportData, $reportData);

        $report->userReports()->create(array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]));

        return $report->refresh();
    }

    public function checkReportExist(User $context, int $reportId): bool
    {
        return $this->reportOwnerUserRepository()->getModel()->newQuery()
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType())
            ->where('report_id', $reportId)
            ->exists();
    }

    public function updateReportOwner(User $context, int $id, array $attributes): bool
    {
        $report = $this->with(['owner', 'item'])->find($id);
        gate_authorize($context, 'viewReportContent', $report->owner, $report->owner);

        //Remove reported post
        if ($report->total_report > 0) {
            $report->userReports()->delete();
            $report->update(['total_report' => 0]);

            if (empty($attributes['keep_post'])) {
                // handle remove feed
                app('events')->dispatch('activity.removed_feed', [$report->item], true);
            }
        }

        return true;
    }

    public function getReportByItem(int $itemId, string $itemType): ?Model
    {
        return $this->getModel()->newQuery()
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->first();
    }

    /**
     * @param User $context
     * @param int  $reportId
     * @return array|Collection
     * @throws AuthorizationException
     */
    public function viewUsers(User $context, int $reportId): array|Collection
    {
        $owner = $this->find($reportId)->owner;
        gate_authorize($context, 'viewReportContent', $owner, $owner);
        $query = $this->reportOwnerUserRepository()->getModel()->newQuery();

        return $query->with(['reason', 'userEntity'])
            ->where('report_id', $reportId)
            ->get();
    }
}
