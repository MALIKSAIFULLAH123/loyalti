<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedAgg;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Models\SavedListData;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class DeleteSavedItemForUserTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $savedList = SavedList::factory()->setUser($user)->create();
        $saved = Saved::factory()->setItem($item)->setUser($user)->create(['savedLists' => [$savedList->entityId()]]);
        $this->assertInstanceOf(SavedRepository::class, $repository);
        $this->assertTrue($savedList->refresh()->saved_id == $saved->entityId());

        return [$user, $savedList, $saved, $item, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     */
    public function testSuccess(array $data)
    {
        /**
         * @var User                     $user
         * @var SavedList                $savedList
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, $savedList, $saved, , ] = $data;

        $user->delete();
        $this->assertEmpty(Saved::query()->find($saved->entityId()));
        $this->assertEmpty(SavedList::query()->find($savedList->entityId()));

        $this->assertEmpty(SavedAgg::query()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_type' => $saved->itemType(),
        ])->first());

        $this->assertEmpty(SavedListData::query()->where([
            'saved_id' => $saved->entityId(),
        ])->first());
    }
}
