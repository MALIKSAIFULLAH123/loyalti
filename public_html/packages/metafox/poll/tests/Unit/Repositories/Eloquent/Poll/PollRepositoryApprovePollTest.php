<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use MetaFox\Core\Policies\Handlers\CanApprove;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use Tests\TestCase;

class PollRepositoryApprovePollTest extends TestCase
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
    public function testApprovePoll()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($user);

        /** @var Poll $poll */
        $poll = Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => 0,
        ]);

        $repository = resolve(PollRepositoryInterface::class);

        $this->mock(CanApprove::class)
            ->shouldReceive('check')
            ->andReturn(true);

        $result     = $repository->approve($user, $poll->entityId());

        $this->assertTrue((bool) $result->is_approved);
    }
}

// end
