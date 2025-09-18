<?php

namespace MetaFox\Video\Tests\Unit\Repositories\Eloquent\Video;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Repositories\Eloquent\VideoRepository;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use Tests\TestCase;

class UpdateVideoTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateInstance(): array
    {
        $service = resolve(VideoRepositoryInterface::class);
        $this->assertInstanceOf(VideoRepository::class, $service);

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertInstanceOf(User::class, $user);

        $item = Model::factory()->setUser($user)->setOwner($user)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        return [$service, $user, $item];
    }

    /**
     * @depends testCreateInstance
     * @param  array<int, mixed>      $data
     * @return array<int, mixed>
     * @throws AuthorizationException
     */
    public function testUpdateVideoWithNewTitle(array $data): array
    {
        /** @var Model $item */
        /** @var VideoRepository $repository */
        [$repository, $user, $item] = $data;

        $category = Category::factory()->create();
        $this->assertInstanceOf(Category::class, $category);

        $newTitle = $item->title . now()->timestamp;
        $params   = [
            'title'      => $newTitle,
            'categories' => [$category->entityId()],
            'temp_file'  => 0,
            'privacy'    => MetaFoxPrivacy::EVERYONE,
        ];

        $result = $repository->updateVideo($user, $item->entityId(), $params);
        $this->assertNotSame($item->title, $result->title);

        $returnedCategory = $result->categories->first();
        $this->assertNotEmpty($returnedCategory);
        $this->assertSame($category->entityId(), $returnedCategory->entityId());

        return [$repository, $user, $result];
    }

    /**
     * @depends testUpdateVideoWithNewTitle
     * @param  array<int, mixed>      $data
     * @return array<int, mixed>
     * @throws AuthorizationException
     */
    public function testUpdateVideoWithNewPrivacy(array $data): array
    {
        /** @var Model $item */
        /** @var VideoRepository $repository */
        [$repository, $user, $item] = $data;

        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user)->create();
        FriendFactory::new()->setUser($user)->setOwner($user1)->create();

        $list1 = FriendList::factory()->setUser($user)->create();
        $list2 = FriendList::factory()->setUser($user)->create();

        FriendListDataFactory::new(['list_id' => $list1->id])->setUser($user1)->create();
        FriendListDataFactory::new(['list_id' => $list2->id])->setUser($user1)->create();

        $newTitle = $item->title . now()->timestamp;
        $params   = [
            'title'     => $newTitle,
            'temp_file' => 0,
            'privacy'   => MetaFoxPrivacy::CUSTOM,
            'list'      => [$list1->id, $list2->id],
        ];

        $result = $repository->updateVideo($user, $item->entityId(), $params);
        $this->assertNotEmpty($result);
        $this->assertSame(MetaFoxPrivacy::CUSTOM, $result->privacy);

        return $data;
    }
}
