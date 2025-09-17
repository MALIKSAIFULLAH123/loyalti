<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Result;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\Paginator;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Repositories\Eloquent\ResultRepository;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;
use Tests\TestCase;

class ResultRepositoryActionViewResultsTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(ResultRepositoryInterface::class);
        $this->assertInstanceOf(ResultRepository::class, $repository);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewResults()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        Result::factory()->setQuiz($quiz)->setUser($user)->create();
        Result::factory()->setQuiz($quiz)->setUser($user1)->create();

        /** @var ResultRepository $repository */
        $repository = resolve(ResultRepositoryInterface::class);
        $items      = $repository->viewResults($user, [
            'quiz_id' => $quiz->entityId(),
            'limit'   => 10,
        ]);
        $this->assertInstanceOf(Paginator::class, $items);
        $this->assertNotEmpty($items->items());
    }
}

// end
