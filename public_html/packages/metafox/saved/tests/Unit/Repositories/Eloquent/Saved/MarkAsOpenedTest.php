<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class MarkAsOpenedTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item       = $this->contentFactory()->setUser($user)->setOwner($user)->create();
        $saved      = Saved::factory()->setItem($item)->setUser($user)->create(['is_opened' => false]);
        $this->assertInstanceOf(SavedRepository::class, $repository);

        return [$user, $saved, $repository];
    }

    /**
     * @depends testInstance
     * @param array<int, mixed> $data
     */
    public function testMarkAsOpened(array $data)
    {
        /**
         * @var User                     $user
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, $saved, $repository] = $data;

        $this->actingAs($user);

        $this->assertFalse($saved->is_opened);
        $repository->markAsOpened($user, $saved->entityId());

        $saved->refresh();
        $this->assertTrue($saved->is_opened);
    }
}
