<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use Tests\TestCase;

class PollRepositoryFeaturePollTest extends TestCase
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
    public function testFeaturePoll(): Poll
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
        ]);
        $repository = resolve(PollRepositoryInterface::class);
        $result     = $repository->feature($user, $poll->entityId(), 1);
        $this->assertTrue($result);

        $poll->refresh();
        $this->assertNotEmpty($poll->is_featured);

        return $poll;
    }

    /**
     * @depends testFeaturePoll
     * @throws AuthorizationException
     */
    public function testRemovePollFeature(Poll $poll)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $repository = resolve(PollRepositoryInterface::class);
        $result     = $repository->feature($user, $poll->entityId(), 0);
        $this->assertTrue($result);

        $poll->refresh();
        $this->assertEmpty($poll->is_featured);
    }
}

// end
