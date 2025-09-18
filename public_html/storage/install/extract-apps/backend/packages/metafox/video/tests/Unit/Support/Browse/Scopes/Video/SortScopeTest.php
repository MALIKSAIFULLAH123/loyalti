<?php

namespace MetaFox\Video\Tests\Unit\Support\Browse\Scopes\Video;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Support\Browse\Scopes\Video\ViewScope;
use Tests\TestCase;

class SortScopeTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateResource(): array
    {
        $videoRepository = resolve(VideoRepositoryInterface::class);
        $this->assertInstanceOf(VideoRepositoryInterface::class, $videoRepository);

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertInstanceOf(User::class, $user);
        $this->actingAs($user);

        $video1 = Video::factory()->setUser($user)->setOwner($user)->create(['title' => 'A letter']);
        $video2 = Video::factory()->setUser($user)->setOwner($user)->create(['title' => 'Z letter']);
        $this->assertInstanceOf(Video::class, $video1);
        $this->assertInstanceOf(Video::class, $video2);

        return [$videoRepository, $user, [$video1, $video2]];
    }

    /**
     * @depends testCreateResource
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testSortAToZ(array $data): array
    {
        [$repository, $user] = $data;

        $params = [
            'q'           => '',
            'tag'         => '',
            'sort'        => Browse::SORT_A_TO_Z,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'view'        => ViewScope::VIEW_DEFAULT,
            'limit'       => Pagination::DEFAULT_ITEM_PER_PAGE,
            'category_id' => 0,
            'user_id'     => 0,
        ];

        $this->actingAs($user);
        $results = $repository->viewVideos($user, $user, $params);
        $this->assertNotEmpty($results);

        return [$repository, $user, $params];
    }

    /**
     * @depends testSortAToZ
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testSortZToA(array $data): array
    {
        [$repository, $user, $params] = $data;

        $this->actingAs($user);

        $params['sort'] = Browse::SORT_Z_TO_A;
        $results        = $repository->viewVideos($user, $user, $params);
        $this->assertNotEmpty($results);

        return $data;
    }
}
