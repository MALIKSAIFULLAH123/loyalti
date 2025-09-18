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

class PollRepositoryGetPollByAnswerIdTest extends TestCase
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

        $poll = Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->assertInstanceOf(Poll::class, $poll);

        $answer = Answer::factory()->setPoll($poll)->create();

        return [$repository, $user, $poll, $answer];
    }

    /**
     * @depends testInstance
     * @param  array<int, mixed>      $data
     * @return array<int, mixed>
     * @throws AuthorizationException
     */
    public function testGetPollByAnswerId(array $data): array
    {
        /** @var PollRepository $repository */
        [$repository, $user, $poll, $answer] = $data;

        $result = $repository->getPollByAnswerId($user, $answer->entityId());
        $this->assertTrue($result->entityId() == $poll->entityId());

        return $data;
    }
}

// end
