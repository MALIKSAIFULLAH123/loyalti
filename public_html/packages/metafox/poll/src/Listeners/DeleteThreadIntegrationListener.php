<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;

class DeleteThreadIntegrationListener
{
    /** @var PollRepository */
    private $repository;

    public function __construct(PollRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User                                           $user
     * @param  string                                         $entityType
     * @param  int                                            $entityId
     * @return bool|null
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle(User $user, string $entityType, int $entityId): ?bool
    {
        if ($entityType != Poll::ENTITY_TYPE) {
            return null;
        }

        $this->repository->deletePoll($user, $entityId);

        return true;
    }
}
