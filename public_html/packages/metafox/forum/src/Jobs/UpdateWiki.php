<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Notifications\DisplayWiki;
use MetaFox\Forum\Repositories\ForumThreadSubscribeRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;

class UpdateWiki extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var ForumThread
     */
    protected $thread;

    /**
     * @var User
     */
    protected $context;

    /**
     * @var bool
     */
    protected $isWiki;

    public function __construct(User $context, ?ForumThread $thread, bool $isWiki)
    {
        parent::__construct();
        $this->thread  = $thread;
        $this->context = $context;
        $this->isWiki  = $isWiki;
    }

    public function handle()
    {
        $thread = $this->thread;

        if (null === $thread) {
            return null;
        }

        $user = $this->context;

        $subscribes = resolve(ForumThreadSubscribeRepositoryInterface::class)
            ->getSubscribersOfThreads([$thread->entityId()]);

        if ($subscribes->count() == 0) {
            return null;
        }

        foreach ($subscribes as $subscribe) {
            $subscribedUser   = $subscribe->user;
            $subscribedUserId = $subscribedUser->entityId();

            if ($user->entityId() != $subscribedUserId) {
                $notification = new DisplayWiki($thread);
                $notification->setIsWiki($this->isWiki);
                $notification->setSubscribedUserId($subscribedUserId);
                $notificationParams = [$subscribedUser, $notification];
                Notification::send(...$notificationParams);
            }
        }
    }
}
