<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Result;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Models\Result;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\Eloquent\ResultRepository;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;
use Tests\TestCase;

class ResultRepositoryUpdateResultTest extends TestCase
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

        return [$poll, $answer1, $answer2];
    }

    /**
     * @depends testGenerateItems
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testUpdateResult(array $params)
    {
        /** @var Poll $poll */
        [$poll, $answer1, $answer2] = $params;
        $user                       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $this->skipPolicies(PollPolicy::class);

        /** @var Result $deleteResult */
        $deleteResult = Result::factory()->setUser($user)->setPoll($poll)->setAnswer($answer1)->create();

        /** @var ResultRepository $repository */
        $repository = resolve(ResultRepositoryInterface::class);
        $result     = $repository->updateResult($user, $poll->entityId(), [
            'answers' => [$answer2->entityId()],
        ]);

        $this->assertInstanceOf(Poll::class, $result);
    }
}

// end
