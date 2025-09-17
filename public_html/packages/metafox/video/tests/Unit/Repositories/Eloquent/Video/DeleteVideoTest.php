<?php

namespace MetaFox\Video\Tests\Unit\Repositories\Eloquent\Video;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Models\VideoText;
use MetaFox\Video\Repositories\Eloquent\VideoRepository;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use Tests\TestCase;

class DeleteVideoTest extends TestCase
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
     * @throws AuthorizationException
     */
    public function testDeleteVideo(array $data)
    {
        /** @var Model $item */
        /** @var VideoRepository $repository */
        [$repository, $user, $item] = $data;

        $repository->deleteVideo($user, $item->entityId());

        $this->assertEmpty(Model::query()->find($item->entityId()));
        $this->assertEmpty(VideoText::query()->find($item->entityId()));
    }
}
