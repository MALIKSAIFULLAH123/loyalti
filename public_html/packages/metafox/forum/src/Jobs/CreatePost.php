<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Notifications\CreatePost as PostNotification;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Jobs\AbstractJob;

class CreatePost extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var ForumThread
     */
    protected $threadId;

    /**
     * @var array
     */
    protected $postId;

    public function __construct(int $threadId, int $postId)
    {
        parent::__construct();
        $this->threadId = $threadId;

        $this->postId = $postId;
    }

    public function handle()
    {
        $thread = resolve(ForumThreadRepositoryInterface::class)->find($this->threadId);

        $post = resolve(ForumPostRepositoryInterface::class)->find($this->postId);

        $user = $post->user;

        if (null === $thread || null === $post) {
            return null;
        }

        $subscribes = $thread->subscribes()->get();

        if (null !== $subscribes) {
            foreach ($subscribes as $subscribe) {
                $subscribedUser = $subscribe->user;
                if ($user->entityId() != $subscribedUser->entityId()) {
                    $response = [$subscribedUser, new PostNotification($post)];
                    Notification::send(...$response);
                }
            }
        }
    }
}
