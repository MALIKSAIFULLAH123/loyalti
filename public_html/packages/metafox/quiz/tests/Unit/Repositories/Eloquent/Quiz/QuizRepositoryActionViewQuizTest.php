<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Quiz;

use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use Tests\TestCase;

class QuizRepositoryActionViewQuizTest extends TestCase
{
    public function testInstance(): Model
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);
        $this->assertNotEmpty($item);

        return $item;
    }

    /**
     * @depends testInstance
     */
    public function testViewQuiz(Model $item)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $repository = resolve(QuizRepositoryInterface::class);
        $result     = $repository->viewQuiz($user, $item->id);

        $this->assertTrue(($item->id == $result->id));
    }
}

// end
