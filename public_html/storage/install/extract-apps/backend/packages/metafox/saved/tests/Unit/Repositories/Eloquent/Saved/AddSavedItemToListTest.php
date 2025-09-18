<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class AddSavedItemToListTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item       = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $saved      = Saved::factory()->setItem($item)->setUser($user)->create();
        $list       = SavedList::factory()->setUser($user)->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(SavedRepository::class, $repository);
        $this->assertInstanceOf(Saved::class, $saved);
        $this->assertInstanceOf(SavedList::class, $list);

        return [$repository, $user, $saved, $list];
    }

    /**
     * @depends testInstance
     *
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testAddToList(array $data): array
    {
        /**
         * @var SavedRepositoryInterface $repository
         * @var User                     $user
         * @var Saved                    $saved
         * @var SavedList                $list
         */
        [$repository, $user, $saved, $list] = $data;

        $this->assertEmpty($saved->savedLists->count());

        $repository->addToList($user, $saved->entityId(), [$list->entityId()]);

        $saved->refresh();
        $this->assertNotEmpty($saved->savedLists->count());

        return $data;
    }

    /**
     * @depends testInstance
     *
     * @param  array<int, mixed>        $data
     * @return array<int,        mixed>
     */
    public function testAddToListWithSameListItem(array $data): array
    {
        /**
         * @var SavedRepositoryInterface $repository
         * @var User                     $user
         * @var Saved                    $saved
         * @var SavedList                $list
         */
        [$repository, $user, , $list] = $data;

        $item  = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $saved = Saved::factory()->setItem($item)->setUser($user)->create();

        $this->assertInstanceOf(Saved::class, $saved);
        $this->assertEmpty($saved->savedLists->count());

        $repository->addToList($user, $saved->entityId(), [$list->entityId()]);

        $saved->refresh();
        $this->assertNotEmpty($saved->savedLists->count());

        return $data;
    }
}
