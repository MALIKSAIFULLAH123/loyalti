<?php

namespace MetaFox\Blog\Tests\Unit\Repositories\Eloquent\Blog;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Models\Category;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewScope;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * Class BlogRepositoryActionViewBlogs.
 */
class BlogRepositoryActionViewBlogs extends TestCase
{
    public function testInstance(): array
    {
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);
        $this->assertTrue(true);

        Model::factory()->count(2)->create(['privacy' => 0]);

        $items = Model::query()->paginate(Pagination::DEFAULT_ITEM_PER_PAGE);
        $this->assertTrue($items->isNotEmpty());

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
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAllBlog(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);
        $items      = $repository->viewBlogs($user, $user, $params);
        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testWithTag(array $params): void
    {
        $user    = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $tagText = $this->faker->word;

        $blog = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);

        $tag = Tag::factory()->setText($tagText)->create();

        $blog->tagData()->syncWithPivotValues([$tag->entityId()], ['item_type' => $blog->entityType()]);

        $params['tag'] = Str::lower(Str::slug($tagText));
        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $items = $repository->viewBlogs($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @param  array                         $params
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testViewAllBlogWithOwnerId(array $params): array
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user2)->setOwner($user2)->create(['privacy' => 0]);

        /** @var BlogRepository $repository */
        $repository        = resolve(BlogRepositoryInterface::class);
        $params['user_id'] = $user2->entityId();
        $items             = $repository->viewBlogs($user, $user2, $params);

        $this->assertTrue($items->isNotEmpty());

        return [$repository, $user, $user2];
    }

    /**
     * @depends testInstance
     * @depends testViewAllBlogWithOwnerId
     * @throws AuthorizationException
     */
    public function testViewDraftBlogWithOwnerId(array $params, array $resources): void
    {
        /** @var BlogRepositoryInterface $repository */
        [$repository, $user, $user2] = $resources;
        $this->be($user);

        $draftBlog = Model::factory()->setUser($user2)->setOwner($user2)->create([
            'is_draft' => 1,
            'privacy'  => 0,
        ]);

        // View on other profile
        $params['user_id'] = $user2->entityId();
        $items             = $repository->viewBlogs($user, $user2, $params);
        $itemIds           = array_column($items->items(), 'id');
        $this->assertNotInArray($draftBlog->entityId(), $itemIds);

        // View on their profile
        $this->be($user2);
        $params['user_id'] = $user2->entityId();
        $items             = $repository->viewBlogs($user2, $user2, $params);
        $itemIds           = array_column($items->items(), 'id');
        $this->assertNotInArray($draftBlog->entityId(), $itemIds);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAllBlogWithCategory(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'categories' => [$category->id]]);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $params['category_id'] = $category->id;
        $items                 = $repository->viewBlogs($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    public static function prepareDataForViewAllBlogWithSortAndWhen(): array
    {
        return [
            [['sort' => Browse::SORT_MOST_VIEWED]],
            [['sort' => Browse::SORT_MOST_DISCUSSED]],
            [['sort' => Browse::SORT_MOST_LIKED]],
            [['when' => Browse::WHEN_TODAY]],
            [['when' => Browse::WHEN_THIS_MONTH]],
            [['when' => Browse::WHEN_THIS_WEEK]],
        ];
    }

    /**
     * @depends      testInstance
     * @dataProvider prepareDataForViewAllBlogWithSortAndWhen
     *
     * @param array<string, mixed> $params
     *
     * @throws AuthorizationException
     */
    public function testViewAllBlogWithSortAndWhen(array $data, array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $params = array_merge($params, $data);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $items = $repository->viewBlogs($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    public static function prepareDataForViewAllBlogWithView(): array
    {
        return [
            [['view' => Browse::VIEW_MY]],
            [['view' => Browse::VIEW_PENDING]],
            [['view' => ViewScope::VIEW_DRAFT]],
            [['view' => Browse::VIEW_FRIEND]],
        ];
    }

    /**
     * @depends      testInstance
     * @dataProvider prepareDataForViewAllBlogWithView
     *
     * @param array<string, mixed> $params
     *
     * @throws AuthorizationException
     */
    public function testViewAllBlogWithView(array $data, array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $params = array_merge($params, $data);

        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'is_draft' => true]);
        Model::factory()->setUser($user1)->setOwner($user1)->create(['privacy' => 0, 'is_approved' => false]);
        Model::factory()->setUser($user1)->setOwner($user1)->create(['privacy' => 0]);

        FriendFactory::new()->setUser($user)->setOwner($user1)->create();
        FriendFactory::new()->setUser($user1)->setOwner($user)->create();

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);
        $items      = $repository->viewBlogs($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllBlogWithSearchQuery(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $title = $this->faker->title;
        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'title' => $title]);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $params['q'] = $title;
        $items       = $repository->viewBlogs($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllBlogEmpty(array $params): void
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $items = $repository->viewBlogs($user, $user2, $params);

        $this->assertTrue($items->isEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllBlogWithViewFeature(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'is_featured' => Blog::IS_FEATURED]);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $params['view'] = Browse::VIEW_FEATURE;
        $items          = $repository->viewBlogs($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllBlogWithViewSponsor(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'is_sponsor' => Blog::IS_SPONSOR]);

        /** @var BlogRepository $repository */
        $repository = resolve(BlogRepositoryInterface::class);

        $params['view'] = Browse::VIEW_SPONSOR;
        $items          = $repository->viewBlogs($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }
}
