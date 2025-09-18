<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent;

use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class PageRepositoryViewPagesTest extends TestCase
{
    protected PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageRepositoryInterface::class);
    }

    public function testInstance()
    {
        $repository = resolve(PageRepositoryInterface::class);
        $this->assertInstanceOf(PageRepository::class, $repository);

        return [
            'q'           => '',
            'view'        => Browse::VIEW_PENDING,
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'category_id' => 0,
            'type_id'     => 0,
            'user_id'     => 0,
            'limit'       => Pagination::DEFAULT_ITEM_PER_PAGE,
        ];
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewMyPagesWithSponsoredPages(array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);
        Model::factory()->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_SPONSOR,
        ]);

        Model::factory()->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_UN_SPONSOR,
        ]);

        $mySponsoredItem = Model::factory()->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_SPONSOR,
        ]);

        $myNormalItem = Model::factory()->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_UN_SPONSOR,
        ]);

        $params = array_merge($params, [
            'view' => Browse::VIEW_MY,
        ]);

        $this->actingAs($owner);

        $results = $this->repository->viewPages($owner, $owner, $params)->collect();

        $this->assertNotEmpty($results);
        $this->assertTrue($results->contains('id', $mySponsoredItem->entityId()));
        $this->assertTrue($results->contains('id', $myNormalItem->entityId()));
        $this->assertCount(2, $results); // should not contain another's items
    }
}
