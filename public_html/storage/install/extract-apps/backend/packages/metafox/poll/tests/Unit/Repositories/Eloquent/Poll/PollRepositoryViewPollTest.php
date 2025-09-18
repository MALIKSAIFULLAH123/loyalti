<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use Tests\TestCase;

class PollRepositoryViewPollTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        return [$user, $repository];
    }

    /**
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testViewPoll(array $data)
    {
        /**
         * @var User                    $user
         * @var PollRepositoryInterface $repository
         */
        [$user, $repository] = $data;

        $poll = Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $result = $repository->viewPoll($user, $poll->entityId());

        $this->assertTrue($result->entityId() == $poll->entityId());
    }

    /**
     * Authorization case.
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testViewPollAuthorization(array $data)
    {
        /**
         * @var User                    $user
         * @var PollRepositoryInterface $repository
         */
        [$user, $repository] = $data;
        $owner               = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $poll = Poll::factory()
            ->setOwner($owner)
            ->setUser($owner)->create([
                'privacy' => MetaFoxPrivacy::ONLY_ME,
            ]);

        $this->expectException(AuthorizationException::class);
        $repository->viewPoll($user, $poll->entityId());
    }

    /**
     * NotFound case.
     * @depends testInstance
     *
     * @param array<int, mixed> $data
     *
     * @throws AuthorizationException
     */
    public function testViewPollNotFound(array $data)
    {
        /**
         * @var User                    $user
         * @var PollRepositoryInterface $repository
         */
        [$user, $repository] = $data;

        $this->expectException(ModelNotFoundException::class);
        $repository->viewPoll($user, 0);
    }
}
