<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;

class ThreadIntegrationEditListener
{
    /** @var PollRepository */
    private $repository;

    public function __construct(PollRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User   $context
     * @param  string $entityType
     * @param  int    $entityId
     * @return array
     */
    public function handle(User $context, string $entityType, int $entityId, ?string $attachedPermissionName = null): array
    {
        if ($entityType != Poll::ENTITY_TYPE) {
            return [];
        }

        return $this->repository->getDataForEditIntegration($context, $entityId, $attachedPermissionName);
    }
}
