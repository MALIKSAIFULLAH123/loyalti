<?php

namespace MetaFox\Like\Tests\Feature\Notifications;

use MetaFox\Comment\Models\Comment;
use MetaFox\Notification\Http\Resources\v1\Notification\NotificationItem;
use MetaFox\Notification\Models\Notification;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Tests\Mock\Models\ContentModel;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class CommentNotificationTest extends TestCase
{
    public function testCreateInstance()
    {
        $this->markTestIncomplete();

        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $item = ContentModel::factory()->setUser($user)->setOwner($user)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        Comment::factory()->setUser($user)->setItem($item)->create([
            'is_approved' => 1,
        ]);

        $notification = Notification::query()->where(['notifiable_id' => $user->entityId(), 'notifiable_type' => $user->entityType()])->first();
        $this->assertNull($notification);

        Comment::factory()->setUser($user2)->setItem($item)->create([
            'is_approved' => 1,
        ]);
        $notification = Notification::query()->where(['notifiable_id' => $user->entityId(), 'notifiable_type' => $user->entityType()])->first();
        $this->assertInstanceOf(Notification::class, $notification);

        $resource = (new NotificationItem($notification))->toArray(null);

        $message = $this->localize('comment::phrase.user_commented_on_your_post_title', [
            'user'  => $user2->userEntity->name,
            'title' => $item->toTitle(),
        ]);

        $this->assertSame($message, $resource['message']);
    }
}
