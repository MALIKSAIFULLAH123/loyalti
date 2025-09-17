<?php

namespace MetaFox\Video\Tests\Unit\Repositories\Eloquent\Video;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Repositories\Eloquent\VideoRepository;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Support\Browse\Scopes\Video\ViewScope;
use Tests\TestCase;

class ViewVideosTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateInstance(): array
    {
        $service = resolve(VideoRepositoryInterface::class);
        $this->assertInstanceOf(VideoRepository::class, $service);

        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user3 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertInstanceOf(User::class, $user1);
        $this->assertInstanceOf(User::class, $user2);
        $this->assertInstanceOf(User::class, $user3);

        $params = [
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

        return [$service, [$user1, $user2, $user3], $params];
    }

    /**
     * @depends testCreateInstance
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testViewVideos(array $data): array
    {
        /** @var VideoRepository $repository */
        [$repository, $users, $params] = $data;
        [$user1]                       = $users;

        $this->actingAs($user1);
        $category = Category::factory()->create();
        $this->assertInstanceOf(Category::class, $category);

        $itemCount = 2;

        Model::factory()->count($itemCount)->setOwner($user1)->setUser($user1)->create([
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'categories' => [$category->entityId()],
        ]);

        $params['category_id'] = $category->entityId();

        $results = $repository->viewVideos($user1, $user1, $params);
        $this->assertTrue($results->isNotEmpty());

        return $data;
    }

    /**
     * @depends testViewVideos
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testViewAllVideosWithOwner(array $data): array
    {
        /** @var VideoRepository $repository */
        [$repository, $users, $params] = $data;
        [$user1, $user2]               = $users;

        $this->actingAs($user1);

        Model::factory()->setUser($user2)->setOwner($user2)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $params['user_id'] = $user2->entityId();

        $items = $repository->viewVideos($user1, $user2, $params);
        $this->assertTrue($items->isNotEmpty());

        return $data;
    }

    /**
     * @depends testViewAllVideosWithOwner
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testViewAllVideosWithSearch(array $data): array
    {
        /** @var VideoRepository $repository */
        [$repository, $users, $params] = $data;
        [$user1]                       = $users;

        $search = $this->faker->word;

        $this->actingAs($user1);

        Model::factory()->setUser($user1)->setOwner($user1)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
            'title'   => $search,
        ]);
        $params['q'] = $search;

        $items = $repository->viewVideos($user1, $user1, $params);
        $this->assertTrue($items->isNotEmpty());

        return $data;
    }
}
