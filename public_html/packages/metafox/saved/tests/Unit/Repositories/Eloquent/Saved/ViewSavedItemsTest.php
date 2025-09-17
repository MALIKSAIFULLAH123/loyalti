<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class ViewSavedItemsTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item       = ContentModel::factory()->setUser($user)->setOwner($user)->create();
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
     *
     * @throws AuthorizationException
     */
    public function testSuccess(array $data): void
    {
        /**
         * @var User                     $user
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, , $saved, , $repository] = $data;

        $params = [
            'q'             => null,
            'open'          => false,
            'limit'         => 2,
            'collection_id' => 0,
            'sort_type'     => SortScope::SORT_TYPE_DEFAULT,
            'when'          => WhenScope::WHEN_DEFAULT,
            'type'          => '',
        ];

        $results = $repository->viewSavedItems($user, $params);
        $this->assertNotEmpty($results->items());
        $this->assertArrayHasKey($saved->entityId(), collect($results->items())->pluck([], 'id')->toArray());
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testSuccessWithSavedList(array $data): void
    {
        /**
         * @var User                     $user
         * @var SavedList                $savedList
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, $savedList, $saved, , $repository] = $data;

        $params = [
            'q'             => null,
            'open'          => false,
            'limit'         => 2,
            'collection_id' => $savedList->entityId(),
            'sort_type'     => SortScope::SORT_TYPE_DEFAULT,
            'when'          => WhenScope::WHEN_DEFAULT,
            'type'          => '',
        ];

        $results = $repository->viewSavedItems($user, $params);
        $this->assertNotEmpty($results->items());
        $this->assertArrayHasKey($saved->entityId(), collect($results->items())->pluck([], 'id')->toArray());
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testSuccessWithType(array $data): void
    {
        /**
         * @var User                     $user
         * @var ContentModel             $item
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, , $saved, $item, $repository] = $data;

        $params = [
            'q'             => null,
            'open'          => false,
            'limit'         => 2,
            'collection_id' => 0,
            'sort_type'     => SortScope::SORT_TYPE_DEFAULT,
            'when'          => WhenScope::WHEN_DEFAULT,
            'type'          => $item->entityType(),
        ];

        $results = $repository->viewSavedItems($user, $params);
        $this->assertNotEmpty($results->items());
        $this->assertArrayHasKey($saved->entityId(), collect($results->items())->pluck([], 'id')->toArray());
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testSuccessWithTypeAndSavedList(array $data): void
    {
        /**
         * @var User                     $user
         * @var SavedList                $savedList
         * @var ContentModel             $item
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, $savedList, $saved, $item, $repository] = $data;

        $params = [
            'q'             => null,
            'open'          => false,
            'limit'         => 2,
            'collection_id' => $savedList->entityId(),
            'sort_type'     => SortScope::SORT_TYPE_DEFAULT,
            'when'          => WhenScope::WHEN_DEFAULT,
            'type'          => $item->entityType(),
        ];

        $results = $repository->viewSavedItems($user, $params);
        $this->assertNotEmpty($results->items());
        $this->assertArrayHasKey($saved->entityId(), collect($results->items())->pluck([], 'id')->toArray());
    }
}
