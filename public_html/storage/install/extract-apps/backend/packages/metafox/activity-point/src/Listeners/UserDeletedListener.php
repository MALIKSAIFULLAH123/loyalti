<?php

namespace MetaFox\ActivityPoint\Listeners;

use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Models\PointStatistic;
use MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface;
use MetaFox\ActivityPoint\Support\PointConversion as Support;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }

        $this->deletePointStatistic($user);
        $this->cancelPendingConversionRequests($user);
    }

    protected function deletePointStatistic(User $user): void
    {
        $repository = resolve(PointStatisticRepositoryInterface::class);

        $statistic = $repository->getModel()->newModelQuery()->where('id', $user->entityId())->first();

        if (!$statistic instanceof PointStatistic) {
            return;
        }

        $statistic->delete();
    }

    protected function cancelPendingConversionRequests(User $user): void
    {
        ConversionRequest::query()
            ->where([
                'user_id' => $user->entityId(),
                'status'  => Support::TRANSACTION_STATUS_PENDING
            ])
            ->get()
            ->each(fn (ConversionRequest $request) => $request->update(['status' => Support::TRANSACTION_STATUS_CANCELLED]));
    }
}
