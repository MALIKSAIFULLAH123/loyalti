<?php

namespace MetaFox\Video\Tests\Unit\Repositories\Eloquent\Video;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Repositories\Eloquent\VideoRepository;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use Tests\TestCase;

class ViewVideoTest extends TestCase
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
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testViewVideo(array $data): array
    {
        [$repository, $user, $item] = $data;

        $result = $repository->viewVideo($user, $item->entityId());
        $this->assertInstanceOf(Content::class, $result);
        $this->assertSame($item->entityId(), $result->entityId());

        return $data;
    }

    /**
     * @depends testViewVideo
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testViewVideoNotExist(array $data): array
    {
        [$repository, $user,] = $data;

        $this->expectException(ModelNotFoundException::class);
        $repository->viewVideo($user, 0);

        return $data;
    }
}
