<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Quiz;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use Tests\TestCase;

class QuizRepositoryActionFeatureQuizTest extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
        $this->assertTrue(true);

        $item = Model::factory()->create([
            'is_featured' => 0,
            'privacy'     => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->assertNotEmpty($item);

        return $item;
    }

    /**
     * @depends testInstance
     *
     * @param Model $item
     *
     * @return Model
     * @throws AuthorizationException
     */
    public function testFeatureQuiz(Model $item): Model
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $repository->feature($admin, $item->entityId(), 1);
        $item->refresh();
        $this->assertNotEmpty($item->is_featured);

        return $item;
    }

    /**
     * @depends testFeatureQuiz
     *
     * @param Model $item
     *
     * @throws AuthorizationException
     */
    public function testRemoveFeatureQuiz(Model $item)
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $repository->feature($admin, $item->entityId(), 0);
        $item->refresh();
        $this->assertEmpty($item->is_featured);
    }
}
