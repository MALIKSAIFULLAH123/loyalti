<?php

namespace MetaFox\Video\Tests\Unit\Repositories\Eloquent\Video;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\TempFileModel;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Models\Category;
use MetaFox\Video\Repositories\Eloquent\VideoRepository;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use Tests\TestCase;

class CreateVideoTest extends TestCase
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
        $this->assertInstanceOf(User::class, $user1);
        $this->assertInstanceOf(User::class, $user2);

        $category = Category::factory()->create();

        $tempFile = $this->createTempFile($user1, 'test.mp4', 'video');
        $this->assertInstanceOf(TempFileModel::class, $tempFile);

        return [$service, [$user1, $user2], $category, $tempFile];
    }

    /**
     * @depends testCreateInstance
     * @param  array<int, mixed> $data
     * @return array<int, mixed>
     * @throws Exception
     */
    public function testCreateVideoWithTempFile(array $data): array
    {
        /** @var VideoRepository $repository */
        /** @var TempFileModel $tempFile */
        [$repository, $users, $category, $tempFile] = $data;
        [$user1,]                                   = $users;

        $params = [
            'categories' => [$category->entityId()],
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'temp_file'  => $tempFile->entityId(),
            'title'      => $this->faker->sentence,
            'text'       => $this->faker->text,
        ];

        $result = $repository->createVideo($user1, $user1, $params);
        $this->assertNotEmpty($result->entityId());

        return $data;
    }

    /**
     * @depends testCreateVideoWithTempFile
     * @param  array<int, mixed> $data
     * @return array<int, mixed>
     * @throws Exception
     */
    public function testCreateVideoWithTempFileOnOwner(array $data): array
    {
        /** @var VideoRepository $repository */
        /** @var TempFileModel $tempFile */
        [$repository, $users, $category, $tempFile] = $data;
        [$user1,]                                   = $users;

        $publicGroup = GroupFactory::new()->setUser($user1)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        $params = [
            'categories' => [$category->entityId()],
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'temp_file'  => $tempFile->entityId(),
            'title'      => $this->faker->sentence,
            'text'       => $this->faker->text,
        ];

        $result = $repository->createVideo($user1, $publicGroup, $params);
        $this->assertNotEmpty($result->entityId());
        $this->assertSame($publicGroup->entityId(), $result->ownerId());

        return $data;
    }

    /**
     * @depends testCreateVideoWithTempFileOnOwner
     * @param  array<int, mixed> $data
     * @return array<int, mixed>
     * @throws Exception
     */
    public function testCreateVideoWithCustomPrivacy(array $data): array
    {
        /** @var VideoRepository $repository */
        /** @var TempFileModel $tempFile */
        [$repository, $users, $category, $tempFile] = $data;
        [$user1, $user2]                            = $users;

        FriendFactory::new()->setUser($user1)->setOwner($user2)->create();
        FriendFactory::new()->setUser($user2)->setOwner($user1)->create();

        $list1 = FriendList::factory()->setUser($user1)->create();
        $list2 = FriendList::factory()->setUser($user1)->create();

        FriendListDataFactory::new(['list_id' => $list1->id])->setUser($user2)->create();
        FriendListDataFactory::new(['list_id' => $list2->id])->setUser($user2)->create();

        $params = [
            'categories' => [$category->entityId()],
            'temp_file'  => $tempFile->entityId(),
            'title'      => $this->faker->sentence,
            'text'       => $this->faker->text,
            'privacy'    => MetaFoxPrivacy::CUSTOM,
            'list'       => [$list1->id, $list2->id],
        ];

        $result = $repository->createVideo($user1, $user1, $params);
        $this->assertNotEmpty($result->entityId());

        return $data;
    }

    /**
     * @depends testCreateVideoWithCustomPrivacy
     * @param  array<int, mixed> $data
     * @return array<int, mixed>
     * @throws Exception
     */
    public function testCreateVideoWithTempFileOnOwnerFail(array $data): array
    {
        /** @var VideoRepository $repository */
        /** @var TempFileModel $tempFile */
        [$repository, $users, $category, $tempFile] = $data;
        [$user1, $user2]                            = $users;

        $params = [
            'categories' => [$category->entityId()],
            'privacy'    => MetaFoxPrivacy::EVERYONE,
            'temp_file'  => $tempFile->entityId(),
            'title'      => $this->faker->sentence,
            'text'       => $this->faker->text,
        ];

        $this->expectException(AuthorizationException::class);
        $repository->createVideo($user1, $user2, $params);

        return $data;
    }
}
