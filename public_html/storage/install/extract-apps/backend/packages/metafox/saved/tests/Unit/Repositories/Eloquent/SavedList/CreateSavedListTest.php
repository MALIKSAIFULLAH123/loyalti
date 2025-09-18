<?php

namespace MetaFox\Saved\Tests\Unit\Repositories\Eloquent\SavedList;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use MetaFox\Saved\Repositories\Eloquent\SavedListRepository;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use Tests\TestCase;

class CreateSavedListTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(SavedListRepositoryInterface::class);
        $this->assertInstanceOf(SavedListRepository::class, $repository);

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        return [$user, $repository];
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
         * @var User                         $user
         * @var SavedListRepositoryInterface $repository
         */
        [$user, $repository] = $attributes;

        $savedList = $repository->createSavedList($user, ['name' => $this->faker->name, 'privacy' => 0]);
        $this->assertNotEmpty($savedList);
    }
}
