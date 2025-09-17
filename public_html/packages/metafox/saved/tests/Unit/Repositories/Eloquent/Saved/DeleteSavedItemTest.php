<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use Illuminate\Auth\Access\AuthorizationException;
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

class DeleteSavedItemTest extends TestCase
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
     *
     * @throws AuthorizationException
     */
    public function testSuccess(array $data)
    {
        /**
         * @var User                     $user
         * @var SavedList                $savedList
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, $savedList, $saved, , $repository] = $data;

        /** @var SavedAgg $SavedAgg */
        $SavedAgg = SavedAgg::query()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_type' => $saved->itemType(),
        ])->first();

        $oldCount = $SavedAgg->total_saved;
        $checkSavedId = 0;

        $repository->deleteSaved($user, $saved->entityId());
        $this->assertEmpty(Saved::query()->find($saved->entityId()));
        $SavedAgg->refresh();
        $this->assertTrue(($oldCount - 1) == $SavedAgg->total_saved);
        $this->assertTrue($savedList->refresh()->saved_id == $checkSavedId);

        $this->assertEmpty(SavedListData::query()->where([
            'saved_id' => $saved->entityId(),
        ])->first());
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testUpdateSavedId(array $data)
    {
        /**
         * @var User                     $user
         * @var SavedList                $savedList
         * @var ContentModel             $item
         * @var SavedRepositoryInterface $repository
         */
        [$user, $savedList, , $item, $repository] = $data;

        $saved = Saved::factory()->setItem($item)->setUser($user)->create(['savedLists' => [$savedList->entityId()]]);
        $saved2 = Saved::factory()->setItem($item)->setUser($user)->create(['savedLists' => [$savedList->entityId()]]);

        /** @var SavedAgg $SavedAgg */
        $SavedAgg = SavedAgg::query()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_type' => $saved->itemType(),
        ])->first();

        $oldCount = $SavedAgg->total_saved;

        $repository->deleteSaved($user, $saved->entityId());
        $this->assertEmpty(Saved::query()->find($saved->entityId()));
        $SavedAgg->refresh();
        $this->assertTrue(($oldCount - 1) == $SavedAgg->total_saved);
        $this->assertTrue($savedList->refresh()->saved_id == $saved2->entityId());
    }
}
