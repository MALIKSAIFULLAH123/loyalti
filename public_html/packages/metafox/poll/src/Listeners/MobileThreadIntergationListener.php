<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\ResourcePermission;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Support\Form\Field\MobileAttachPoll;

class MobileThreadIntergationListener
{
    protected $repository;

    public function __construct(PollRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User        $context
     * @param  User        $owner
     * @param  Entity|null $entity
     * @param  string      $resolution
     * @return array|null
     */
    public function handle(User $context, User $owner, ?Entity $entity, string $resolution): ?array
    {
        if ($resolution != 'mobile') {
            return null;
        }

        $response = [];

        $isEdit = $entity instanceof Poll;

        $granted = true;

        if (!$isEdit) {
            $granted = policy_check(PollPolicy::class, 'create', $context, $owner);
        }

        if (!$granted) {
            return $response;
        }

        $field = new MobileAttachPoll();

        $response = [
            'item_type'      => Poll::ENTITY_TYPE,
            'item_component' => $field,
        ];

        return $response;
    }
}
