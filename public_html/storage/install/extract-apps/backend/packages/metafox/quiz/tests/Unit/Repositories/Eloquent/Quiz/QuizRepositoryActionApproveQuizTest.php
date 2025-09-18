<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Quiz;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use Tests\TestCase;

class QuizRepositoryActionApproveQuizTest extends TestCase
{
    /**
     * @return Model
     */
    public function testInstance(): Model
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
        $this->assertTrue(true);

        $item = Model::factory()->create(['privacy' => MetaFoxPrivacy::EVERYONE, 'is_approved' => 0]);
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
    public function testApproveQuiz(Model $item): Model
    {
        $admin = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $this->actingAs($admin);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $repository->approve($admin, $item->entityId());
        $item->refresh();
        $this->assertNotEmpty($item->is_approved);

        return $item;
    }
}
