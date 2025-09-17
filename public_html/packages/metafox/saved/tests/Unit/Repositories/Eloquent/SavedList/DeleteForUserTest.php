<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\SavedList;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Models\SavedListData;
use MetaFox\Saved\Repositories\Eloquent\SavedListRepository;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use Tests\TestCase;

class DeleteForUserTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedListRepositoryInterface::class);
        $this->assertInstanceOf(SavedListRepository::class, $repository);

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $savedList = SavedList::factory()->setUser($user)->create();
        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $saved = Saved::factory()->setItem($item)->setUser($user)->create(['savedLists' => [$savedList->entityId()]]);
        $this->assertNotEmpty($savedList);
        $this->assertNotEmpty(SavedListData::query()
            ->where([
                'saved_id' => $saved->entityId(),
                'list_id'  => $savedList->entityId(),
            ])->first());

        return [$savedList, $user, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, string> $attributes
     */
    public function testSuccess(array $attributes)
    {
        /**
         * @var SavedList                    $savedList
         * @var User                         $user
         * @var SavedListRepositoryInterface $repository
         */
        [$savedList, $user, $repository] = $attributes;

        $repository->deleteForUser($user);
        $this->assertEmpty(SavedList::query()->find($savedList->entityId()));
        $this->assertEmpty(SavedListData::query()
            ->where('list_id', $savedList->entityId())->first());
    }
}
