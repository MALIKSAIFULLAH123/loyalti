<?php

namespace MetaFox\Group\Tests\Unit\Support\Browse\Scopes\Group;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Group\SortScope;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasFeatureSort;
use MetaFox\Platform\Support\Browse\Contracts\HasTotalMemberSort;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class SortScopeTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     */
    public function testViewDefault(GroupRepositoryInterface $repository)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Group::factory()
            ->setUser($user)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $viewScope = new SortScope();
        $viewScope->setSort(SortScope::SORT_MOST_MEMBER);

        $result = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(4);

        $this->assertNotEmpty($result->items());
    }

    public function testAllowView()
    {
        $data = [
            Browse::SORT_LATEST,
            Browse::SORT_RECENT,
            HasTotalMemberSort::SORT_MOST_MEMBER,
            HasFeatureSort::SORT_FEATURE,
        ];

        foreach (SortScope::getAllowSort() as $sort) {
            $this->assertInArray($sort, $data);
        }
    }
}
