<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use Tests\TestCase;

class PollRepositoryDeletePollTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testDeletePoll()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $poll->answers()->createMany([
            ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 1],
            ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 2],
        ]);

        $this->assertNotEmpty($poll->answers()->get()->toArray());

        $repository = resolve(PollRepositoryInterface::class);
        $result     = $repository->deletePoll($user, $poll->entityId());

        $this->assertTrue((bool) $result);
        $this->assertEmpty(Poll::query()->find($poll->entityId()));
        $this->assertEmpty(Answer::query()->where('poll_id', '=', $poll->entityId())->get());
    }
}

// end
