<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;

class ApproveThreadIntegrationListener
{
    /** @var PollRepository */
    private $repository;

    public function __construct(PollRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  ?string                                        $entityType
     * @param  ?int                                           $entityId
     * @return bool|null
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(?string $entityType, ?int $entityId): ?bool
    {
        if ($entityType != Poll::ENTITY_TYPE) {
            return null;
        }

        if (null === $entityId) {
            return null;
        }

        $poll = $this->repository->find($entityId);
        $poll->update(['is_approved' => 1]);

        return true;
    }
}
