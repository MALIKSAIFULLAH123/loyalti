<?php

namespace MetaFox\Group\Tests\Unit\Repositories\Eloquent;

use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\Eloquent\GroupRepository;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class GroupRepositoryViewGroupsTest extends TestCase
{
    protected GroupRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(GroupRepositoryInterface::class);
    }

    public function testInstance()
    {
        $repository = resolve(GroupRepositoryInterface::class);
        $this->assertInstanceOf(GroupRepository::class, $repository);

        return [
            'q'           => '',
            'view'        => Browse::VIEW_FRIEND,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => 0,
            'limit'       => 10,
        ];
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewMyGroupsWithSponsoredGroups(array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user)->create([
            'privacy'     => PrivacyTypeHandler::PUBLIC,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_SPONSOR,
        ]);

        Model::factory()->setUser($user)->create([
            'privacy'     => PrivacyTypeHandler::PUBLIC,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_UN_SPONSOR,
        ]);

        $mySponsoredItem = Model::factory()->setUser($owner)->create([
            'privacy'     => PrivacyTypeHandler::PUBLIC,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_SPONSOR,
        ]);

        $myNormalItem = Model::factory()->setUser($owner)->create([
            'privacy'     => PrivacyTypeHandler::PUBLIC,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_UN_SPONSOR,
        ]);

        $params = array_merge($params, [
            'view' => Browse::VIEW_MY,
        ]);

        $results = $this->repository->viewGroups($owner, $owner, $params)->collect();

        $this->assertNotEmpty($results);
        $this->assertTrue($results->contains('id', $mySponsoredItem->entityId()));
        $this->assertTrue($results->contains('id', $myNormalItem->entityId()));
        $this->assertCount(2, $results); // should not contain another's items
    }
}
