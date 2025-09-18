<?php

namespace MetaFox\Video\Tests\Unit\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Video\Listeners\FeedComposerEditListener;
use MetaFox\Video\Models\Video;
use Tests\TestCase;

class FeedComposerEditListenerTest extends TestCase
{
    public function testInstance(): array
    {
        $listener = resolve(FeedComposerEditListener::class);

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $video = Video::factory()->setUser($user)->setOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Content::class, $video);
        $this->assertInstanceOf(Content::class, $video->activity_feed);

        return [$listener, $user, $video];
    }

    /**
     * @depends testInstance
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testListenerWithCorrectFeed(array $data): array
    {
        /** @var FeedComposerEditListener $listener */
        /** @var User $user */
        /** @var Video $video */
        [$listener, $user, $video] = $data;
        $feed                      = $video->activity_feed;

        $params = [
            'privacy' => MetaFoxPrivacy::FRIENDS,
            'list'    => [],
        ];

        $return = $listener->handle($user, $user, $feed, $params, null);
        $this->assertNull($return);

        return $data;
    }

    /**
     * @depends testListenerWithCorrectFeed
     * @param  array<int, mixed>             $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testListenerWithIncorrectFeed(array $data): array
    {
        /** @var FeedComposerEditListener $listener */
        /** @var User $user */
        [$listener, $user]   = $data;
        $activityPost        = ActivityFeed::createActivityPost('test', MetaFoxPrivacy::EVERYONE, $user, $user);
        $feed                = $activityPost->activity_feed;
        $this->assertInstanceOf(Feed::class, $feed);

        $return = $listener->handle($user, $user, $feed, [], null);
        $this->assertNull($return);

        return $data;
    }
}
