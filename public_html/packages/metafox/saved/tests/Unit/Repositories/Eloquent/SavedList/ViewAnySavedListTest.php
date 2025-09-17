<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\SavedList;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Repositories\Eloquent\SavedListRepository;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use Tests\TestCase;

class ViewAnySavedListTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedListRepositoryInterface::class);
        $this->assertInstanceOf(SavedListRepository::class, $repository);

        $user      = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $savedList = SavedList::factory()->setUser($user)->create(['privacy' => 0]);
        $this->assertNotEmpty($savedList);

        return [$savedList, $user, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, string> $attributes
     *
     * @throws AuthorizationException
     */
    public function testSuccess(array $attributes)
    {
        $this->markTestIncomplete();

        /**
         * @var SavedList                    $savedList
         * @var User                         $user
         * @var SavedListRepositoryInterface $repository
         */
        [$savedList, $user, $repository] = $attributes;

        $results = $repository->viewSavedLists($user, ['limit' => 2, 'saved_id' => null]);

        $this->assertCount(1, $results->items());
        /** @var SavedList $firstItem */
        $firstItem = $results->items()[0];

        $this->assertTrue($savedList->entityId() == $firstItem->entityId());
    }
}
