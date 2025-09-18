<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\SavedAgg;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Policies\SavedPolicy;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class CreateSavedItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item       = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $this->assertInstanceOf(SavedRepository::class, $repository);

        return [$user, $item, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @return array<int, mixed>
     *
     * @throws AuthorizationException
     */
    public function testCreateSavedItem(array $data): array
    {
        /**
         * @var User                     $user
         * @var ContentModel             $item
         * @var SavedRepositoryInterface $repository
         */
        [$user, $item, $repository] = $data;

        $this->actingAs($user);

        $savedList = SavedList::factory()->setUser($user)->create();

        $params = [
            'item_id'    => $item->entityId(),
            'item_type'  => $item->entityType(),
            'savedLists' => [$savedList->entityId()],
        ];

        $this->skipPolicies(SavedPolicy::class);

        $savedItem = $repository->createSaved($user, $params);
        $this->assertNotEmpty($savedItem);
        $this->assertTrue($savedItem->entityId() == $savedList->refresh()->saved_id);

        return [$user, $item, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testAlreadySaved(array $data)
    {
        /**
         * @var User                     $user
         * @var ContentModel             $item
         * @var SavedRepositoryInterface $repository
         */
        [$user, $item, $repository] = $data;

        $params = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ];

        $this->skipPolicies(SavedPolicy::class);

        $this->expectException(ValidationException::class);
        $repository->createSaved($user, $params);
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testItemNotFound(array $data)
    {
        /**
         * @var User                     $user
         * @var ContentModel             $item
         * @var SavedRepositoryInterface $repository
         */
        [$user, $item, $repository] = $data;

        $params = [
            'item_id'   => 0,
            'item_type' => $item->entityType(),
        ];

        $this->skipPolicies(SavedPolicy::class);

        $this->expectException(\Throwable::class);
        $repository->createSaved($user, $params);
    }

    /**
     * @depends testCreateSavedItem
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testIncrementSavedAgg(array $data)
    {
        /**
         * @var User                     $user
         * @var SavedRepositoryInterface $repository
         */
        [$user, , $repository] = $data;

        $item   = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $params = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
        ];

        $checkCount = 2;
        $this->actingAs($user);
        $this->skipPolicies(SavedPolicy::class);

        $savedItem = $repository->createSaved($user, $params);
        $this->assertNotEmpty($savedItem);

        /** @var SavedAgg $SavedAgg */
        $SavedAgg = SavedAgg::query()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_type' => $item->entityType(),
        ])->first();

        $this->assertNotEmpty($SavedAgg);
        $this->assertTrue($checkCount == $SavedAgg->total_saved);
    }
}
