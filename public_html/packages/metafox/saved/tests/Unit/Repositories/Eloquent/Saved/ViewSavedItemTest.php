<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\Saved;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Repositories\Eloquent\SavedRepository;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use Tests\TestCase;

class ViewSavedItemTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedRepositoryInterface::class);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create();
        $saved = Saved::factory()->setItem($item)->setUser($user)->create();
        $this->assertInstanceOf(SavedRepository::class, $repository);

        return [$user, $saved, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testViewSavedItem(array $data)
    {
        /**
         * @var User                     $user
         * @var Saved                    $saved
         * @var SavedRepositoryInterface $repository
         */
        [$user, $saved, $repository] = $data;

        $result = $repository->viewSavedItem($user, $saved->entityId());

        $this->assertNotEmpty($result);
    }
}
