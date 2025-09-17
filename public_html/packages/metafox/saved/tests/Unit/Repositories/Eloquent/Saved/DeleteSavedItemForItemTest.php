<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use MetaFox\Core\Models\Link;
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

class DeleteSavedItemForItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item       = Link::factory()->setUser($user)->setOwner($user)->create();
        $savedList  = SavedList::factory()->setUser($user)->create();
        $saved      = Saved::factory()->setItem($item)->setUser($user)->create(['savedLists' => [$savedList->entityId()]]);
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
         * @var Saved                    $saved
         * @var ContentModel             $item
         * @var SavedRepositoryInterface $repository
         */
        [$user, , $saved, $item,] = $data;

        /** @var SavedAgg $SavedAgg */
        $SavedAgg = SavedAgg::query()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_type' => $saved->itemType(),
        ])->first();

        $oldCount = $SavedAgg->total_saved;
        $item->delete();
        $this->assertEmpty(Saved::query()->find($saved->entityId()));

        $this->assertTrue(($oldCount - 1) == $SavedAgg->refresh()->total_saved);

        $this->assertEmpty(SavedListData::query()->where([
            'saved_id' => $saved->entityId(),
        ])->first());
    }
}
