<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\PollRepositoryInterface;

class ThreadIntegrationCopyListener
{
    protected $repository;

    public function __construct(PollRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User   $context
     * @param  int    $itemId
     * @param  string $itemType
     * @return int
     */
    public function handle(User $context, int $itemId, string $itemType): ?array
    {
        if (Poll::ENTITY_TYPE !== $itemType) {
            return null;
        }

        return $this->repository->copy($context, $itemId);
    }
}
