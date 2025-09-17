<?php

namespace MetaFox\User\Tests\Unit\Support\Browse\Scopes\Permission;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Support\Browse\Scopes\Poll\SortScope;
use Tests\TestCase;

class SortScopeTest extends TestCase
{
    /**
     * @return PollRepository
     */
    public function testInstance(): PollRepository
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     */
    public function testSortByMostVoted(PollRepository $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($user);

        Poll::factory()->setUserAndOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
            'total_vote'  => 5,
        ]);

        Poll::factory()->setUserAndOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
            'total_vote'  => 10,
        ]);

        $sortScope = new SortScope();
        $sortScope->setSort(SortScope::SORT_MOST_VOTED)->setSortType(SortScope::SORT_TYPE_DEFAULT);

        $result = $repository->getModel()->newQuery()->addScope($sortScope)->simplePaginate(2);
        $this->assertNotEmpty($result->items());
    }
}
