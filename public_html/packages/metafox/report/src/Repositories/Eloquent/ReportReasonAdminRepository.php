<?php

namespace MetaFox\Report\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Report\Models\ReportReason;
use MetaFox\Report\Policies\ReportReasonPolicy;
use MetaFox\Report\Repositories\ReportReasonAdminRepositoryInterface;

/**
 * Class ReportReasonRepository.
 * @method ReportReason getModel()
 * @method ReportReason find($id, $columns = ['*'])
 */
class ReportReasonAdminRepository extends AbstractRepository implements ReportReasonAdminRepositoryInterface
{
    public function model(): string
    {
        return ReportReason::class;
    }

    public function createReason(User $context, array $attributes): ReportReason
    {
        policy_authorize(ReportReasonPolicy::class, 'create', $context);

        $attributes['ordering'] = $this->getNextOrdering();

        $reason = new ReportReason();
        $reason->fill($attributes);
        $reason->save();

        return $reason;
    }

    public function updateReason(User $context, int $id, array $attributes): ReportReason
    {
        policy_authorize(ReportReasonPolicy::class, 'update', $context);

        $reason = $this->find($id);
        $reason->fill($attributes);
        $reason->save();

        return $reason;
    }

    public function viewReasons(User $context): Collection
    {
        policy_authorize(ReportReasonPolicy::class, 'viewAny', $context);

        return $this->getModel()->newQuery()
            ->orderBy('ordering')
            ->orderBy('id')
            ->get()
            ->collect();
    }

    public function deleteReason(User $context, int $id): bool
    {
        policy_authorize(ReportReasonPolicy::class, 'delete', $context);
        $reason = $this->find($id);

        $this->getPhraseRepository()->deleteWhere(['key' => $reason->name]);

        return (bool) $reason->delete();
    }

    /**
     * @inheritDoc
     */
    public function orderReasons(User $context, array $attributes = []): bool
    {
        $ids = Arr::get($attributes, 'order_ids', []);

        if (empty($ids)) {
            return false;
        }

        $ordering = 1;
        foreach ($ids as $id) {
            $this->updateReason($context, $id, ['ordering' => $ordering++]);
        }

        return true;
    }

    protected function getNextOrdering(): int
    {
        $reason = $this->getModel()->newModelQuery()
            ->orderByDesc('ordering')
            ->first();

        if (null === $reason) {
            return 1;
        }

        return (int) $reason->ordering + 1;
    }

    public function getPhraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }
}
