<?php

namespace MetaFox\Group\Support;

use Illuminate\Database\Query\Builder;
use MetaFox\Group\Contracts\MemberContract;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class MemberSupport implements MemberContract
{
    /**
     * @var MemberRepositoryInterface
     */
    protected $repository;

    /**
     * @param MemberRepositoryInterface $repository
     */
    public function __construct(MemberRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getMemberBuilder(User $user, Group $group): Builder
    {
        return $this->repository->getMemberBuilder($user, $group);
    }
}
