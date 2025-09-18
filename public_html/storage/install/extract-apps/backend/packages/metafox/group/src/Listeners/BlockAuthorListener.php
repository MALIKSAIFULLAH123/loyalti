<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\BlockRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class BlockAuthorListener
{
    public function __construct(protected BlockRepositoryInterface $repository) { }

    public function handle(?User $context, User $owner, User $user, array $attributes): ?bool
    {
        if (!$owner instanceof Group) {
            return null;
        }

        $params = [
            'user_id'           => $user->entityId(),
            'delete_activities' => Arr::get($attributes, 'delete_activities', false),
        ];

        return $this->repository->addGroupBlock($context, $owner->entityId(), $params);
    }
}
