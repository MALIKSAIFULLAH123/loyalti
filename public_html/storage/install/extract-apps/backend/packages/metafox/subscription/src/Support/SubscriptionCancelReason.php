<?php

namespace MetaFox\Subscription\Support;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Contracts\SubscriptionCancelReasonContract;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;

class SubscriptionCancelReason implements SubscriptionCancelReasonContract
{
    /**
     * @var SubscriptionCancelReasonRepositoryInterface
     */
    protected $repository;

    public function __construct(SubscriptionCancelReasonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getActiveOptions(User $context): array
    {
        $options = [];

        $reasons = $this->repository->viewActiveReasons($context);

        if ($reasons->count()) {
            foreach ($reasons as $reason) {
                $options[] = [
                    'label' => $reason->toTitle(),
                    'value' => $reason->entityId(),
                ];
            }
        }

        return $options;
    }

    public function hasActiveReasons(User $context): bool
    {
        return count($this->getActiveOptions($context)) > 0;
    }

    public function getDefaultReason(): ?Model
    {
        return $this->repository->getDefaultReason();
    }

    public function clearCaches(): void
    {
        $this->repository->clearCaches();
    }
}
