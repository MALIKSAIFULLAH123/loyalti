<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent;

use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class BlogRepositoryViewBlogsTest extends TestCase
{
    protected BlogRepository $repository;

    public function testRepository(): BlogRepositoryInterface
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepositoryInterface::class, $repository);

        return $repository;
    }

    public function testCreateInstance(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertInstanceOf(User::class, $user);

        $item = Model::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => 1,
            'is_sponsor'  => 0,
        ]);
        $this->assertInstanceOf(Model::class, $item);

        return [$user, $item];
    }

    public function getDefaultSearchParameters()
    {
        return [
            'q'           => '',
            'tag'         => '',
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'view'        => ViewScope::VIEW_DEFAULT,
            'limit'       => Pagination::DEFAULT_ITEM_PER_PAGE,
            'category_id' => 0,
            'user_id'     => 0,
        ];
    }

    /**
     * @depends testRepository
     * @depends testCreateInstance
     *
     * @throws AuthorizationException
     */
    public function testViewMyBlogsWithSponsoredBlogs(BlogRepositoryInterface $repository, array $instances)
    {
        [$user]  = $instances;
        $params  = $this->getDefaultSearchParameters();
        $owner   = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => 1,
            'is_sponsor'  => 1,
        ]);

        $mySponsoredItem = Model::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => 1,
            'is_sponsor'  => 1,
        ]);

        $myNormalItem = Model::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => 1,
            'is_sponsor'  => 0,
        ]);

        $params = array_merge($params, [
            'view' => Browse::VIEW_MY,
        ]);

        $results = $repository->viewBlogs($owner, $owner, $params)->collect();

        $this->assertNotEmpty($results);
        $this->assertTrue($results->contains('id', $mySponsoredItem->entityId()));
        $this->assertTrue($results->contains('id', $myNormalItem->entityId()));
        $this->assertCount(2, $results); // should not contain another's items
    }

    /**
     * @depends testRepository
     * @depends testCreateInstance
     *
     * @throws AuthorizationException
     */
    public function testViewPublicBlogsAsGuest(BlogRepositoryInterface $repository, array $instances)
    {
        [, $item] = $instances;
        $user     = $this->createUser()->assignRole(UserRole::GUEST_USER);
        $params   = $this->getDefaultSearchParameters();

        $results = $repository->viewBlogs($user, $user, $params)->collect();
        $this->assertNotEmpty($results);
        $this->assertTrue($results->contains('id', $item->entityId()));
    }
}
