<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use Tests\TestCase;

class PollRepositoryUpdatePollTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertInstanceOf(User::class, $user);

        return [$repository, $user];
    }

    /**
     * @depends testInstance
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testUpdatePoll(array $data): array
    {
        [$repository, $user] = $data;

        $poll = Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $poll->answers()->createMany([
            ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 1],
            ['answer' => $this->faker->sentence . rand(1, 999), 'ordering' => 2],
        ]);

        $this->assertNotEmpty($poll->entityId());

        $newQuestion = $this->faker->sentence . rand(1, 999);
        $params = [
            'question' => $newQuestion,
        ];

        $updatedPoll = $repository->updatePoll($user, $poll->entityId(), $params);
        $this->assertInstanceOf(Poll::class, $updatedPoll);
        $this->assertEquals($newQuestion, $updatedPoll->question);

        return [$repository, $user, $poll];
    }

    /**
     * @depends testUpdatePoll
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testUpdatePollWithNewAnswerList(array $data): array
    {
        /** @var Poll $poll */
        [$repository, $user, $poll] = $data;

        $newAnswer = $this->faker->sentence . rand(1, 999);
        $poll->refresh();
        $oldAnswers = $poll->answers()->get();
        $deletedAnswer = $oldAnswers->pop();
        $this->assertNotEmpty($poll->entityId());
        $this->assertNotEmpty($oldAnswers);

        $params = [
            'answers' => [
                'changedAnswers' => $oldAnswers->toArray(),
                'newAnswers'     => [
                    [
                        'answer'   => $newAnswer,
                        'ordering' => count($oldAnswers) + 1,
                    ],
                ],
            ],
        ];
        $updatedPoll = $repository->updatePoll($user, $poll->entityId(), $params);

        $this->assertInstanceOf(Poll::class, $updatedPoll);

        $this->assertNull(Answer::query()->find($deletedAnswer->entityId()));

        $isAnswerAdded = Answer::query()->where([
            ['poll_id', '=', $updatedPoll->entityId()],
            ['answer', '=', $newAnswer],
        ])->exists();
        $this->assertTrue($isAnswerAdded);

        return [$repository, $user, $poll];
    }

    /**
     * @depends testUpdatePollWithNewAnswerList
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testUpdatePollWithNewLocation(array $data): array
    {
        [$repository, $user, $poll] = $data;

        $location = [
            'location_address'  => $this->faker->address,
            'location_name'      => $this->faker->address,
            'location_latitude'  => $this->faker->latitude,
            'location_longitude' => $this->faker->longitude,
        ];

        $updatedPoll = $repository->updatePoll($user, $poll->entityId(), $location);

        $this->assertInstanceOf(Poll::class, $updatedPoll);

        $this->assertSame($location['location_address'], $updatedPoll->location_address);
        $this->assertSame($location['location_name'], $updatedPoll->location_name);
        $this->assertSame($location['location_latitude'], (float) $updatedPoll->location_latitude);
        $this->assertSame($location['location_longitude'], (float) $updatedPoll->location_longitude);

        return [$repository, $user, $poll];
    }
}
// end
