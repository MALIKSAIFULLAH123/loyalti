<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class MarkAsUnOpenedTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $saved = Saved::factory()->setItem($item)->setUser($user)->create(['is_opened' => true]);
        $this->assertInstanceOf(SavedRepository::class, $repository);

        return [$user, $saved, $repository];
    }

    /**
     * @depends testInstance
     * @param array<int, mixed> $data
     */
    public function testMarkAsUnOpened(array $data)
    {
        /**
         * @var User                     $user
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, $saved, $repository] = $data;

        $this->assertTrue($saved->is_opened);
        $repository->markAsUnOpened($user, $saved->entityId());

        $saved->refresh();
        $this->assertFalse($saved->is_opened);
    }
}
