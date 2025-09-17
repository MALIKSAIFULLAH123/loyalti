<?php

namespace MetaFox\Report\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Report\Models\ReportReason;
use MetaFox\Report\Policies\ReportReasonPolicy;
use MetaFox\Report\Repositories\ReportReasonRepositoryInterface;

/**
 * Class ReportReasonRepository.
 * @method ReportReason getModel()
 * @method ReportReason find($id, $columns = ['*'])
 */
class ReportReasonRepository extends AbstractRepository implements ReportReasonRepositoryInterface
{
    public function model(): string
    {
        return ReportReason::class;
    }

    public function createReason(User $context, array $attributes): ReportReason
    {
        policy_authorize(ReportReasonPolicy::class, 'create', $context);

        /** @var ReportReason $reason */
        $reason = parent::create($attributes);
        $reason->refresh();

        return $reason;
    }

    public function updateReason(User $context, int $id, array $attributes): ReportReason
    {
        policy_authorize(ReportReasonPolicy::class, 'update', $context);

        $reason = $this->find($id);
        $reason->update($attributes);
        $reason->refresh();

        return $reason;
    }

    public function viewReason(User $context, int $id): ReportReason
    {
        policy_authorize(ReportReasonPolicy::class, 'view', $context);

        return $this->find($id);
    }

    public function viewReasons(User $context, array $attributes): Paginator
    {
        policy_authorize(ReportReasonPolicy::class, 'viewAny', $context);
        $limit = $attributes['limit'];

        return $this->getModel()->newQuery()
            ->orderByDesc('ordering')
            ->orderByDesc('id')
            ->simplePaginate($limit);
    }

    public function deleteReason(User $context, int $id): bool
    {
        policy_authorize(ReportReasonPolicy::class, 'delete', $context);
        $reason = $this->find($id);

        return (bool) $reason->delete();
    }

    /**
     * @throws AuthorizationException
     */
    public function getFormReason(User $context): Collection
    {
        policy_authorize(ReportReasonPolicy::class, 'view', $context);

        return $this->getModel()->newModelInstance()->get();
    }
}
