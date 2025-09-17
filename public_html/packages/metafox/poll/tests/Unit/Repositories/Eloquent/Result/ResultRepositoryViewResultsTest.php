<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Result;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Result;
use MetaFox\Poll\Repositories\Eloquent\ResultRepository;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;
use Tests\TestCase;

class ResultRepositoryViewResultsTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(ResultRepositoryInterface::class);
        $this->assertInstanceOf(ResultRepository::class, $repository);
    }

    /**
     * @return array<int, mixed>
     */
    public function testGenerateItems(): array
    {
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user1);

        $poll = Poll::factory()->setUserAndOwner($user1)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $this->assertInstanceOf(Poll::class, $poll);

        $answer1 = Answer::factory()->setPoll($poll)->create();
        $answer2 = Answer::factory()->setPoll($poll)->create();
        $this->assertInstanceOf(Answer::class, $answer1);
        $this->assertInstanceOf(Answer::class, $answer2);

        $result1 = Result::factory()->setPoll($poll)->setAnswer($answer1)->setUser($user1)->create();
        $result2 = Result::factory()->setPoll($poll)->setAnswer($answer2)->setUser($user2)->create();

        $this->assertInstanceOf(Result::class, $result1);
        $this->assertInstanceOf(Result::class, $result2);

        return [$poll, $answer1];
    }

    /**
     * @depends testGenerateItems
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAllResult(array $params)
    {
        [$poll]  = $params;
        $user    = $poll->user;

        $this->actingAs($user);

        /** @var ResultRepository $repository */
        $repository = resolve(ResultRepositoryInterface::class);
        $items      = $repository->viewResults($user, [
            'poll_id' => $poll->entityId(),
            'limit'   => Pagination::DEFAULT_ITEM_PER_PAGE,
        ]);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testGenerateItems
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewResultsWithAnswerId(array $params)
    {
        [$poll, $answer] = $params;
        $user            = $poll->user;
        $this->actingAs($user);

        /** @var ResultRepository $repository */
        $repository = resolve(ResultRepositoryInterface::class);
        $items      = $repository->viewResults($user, [
            'poll_id'   => $poll->entityId(),
            'answer_id' => $answer->entityId(),
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
        ]);

        $this->assertTrue($items->isNotEmpty());
    }
}

// end
