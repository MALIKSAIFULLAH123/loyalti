<?php

namespace MetaFox\Poll\Tests\Feature\Feed;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class CreatePollFeedTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testCreateInstance(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->assertInstanceOf(User::class, $user);

        $feedRepository = resolve(FeedRepositoryInterface::class);
        $this->assertInstanceOf(FeedRepositoryInterface::class, $feedRepository);

        return [$user, $feedRepository];
    }

    /**
     * @depends testCreateInstance
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testCreatePollThroughFeedComposerWhenSettingIsOn(array $data): array
    {
        /** @var FeedRepositoryInterface $service */
        [$user, $service] = $data;
        $params           = [
            'post_type' => 'poll',
            'question'  => 'Test question',
            'answers'   => [
                ['answer' => 'Answer 1', 'ordering' => 1],
                ['answer' => 'Answer 2', 'ordering' => 2],
            ],
            'content' => 'Test content',
            'privacy' => 0,
        ];
        $feed = $service->createFeed($user, $user, $user, $params);
        $this->assertInstanceOf(Feed::class, $feed);

        return $data;
    }
}
