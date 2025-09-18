<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\SavedList;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Repositories\Eloquent\SavedListRepository;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use Tests\TestCase;

class ViewSavedListTest extends TestCase
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
        /**
         * @var SavedList                    $savedList
         * @var User                         $user
         * @var SavedListRepositoryInterface $repository
         */
        [$savedList, $user, $repository] = $attributes;

        $result = $repository->viewSavedList($user, $savedList->entityId());

        $this->assertNotEmpty($result);
    }
}
