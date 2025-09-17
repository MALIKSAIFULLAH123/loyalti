<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Notifications\MergeThread;
use MetaFox\Forum\Notifications\SubscribedThread as ThreadNotification;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;

class SubscribedThread extends AbstractJob
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
     * @var string
     */
    protected $actionType;

    /**
     * @var array
     */
    protected $actionValue;

    /**
     * @var User
     */
    protected $context;

    /**
     * @var Collection
     */
    protected $subscribers;

    public function __construct(User $context, string $actionType, ?ForumThread $thread, ?array $actionValue = null, ?Collection $subscribers = null)
    {
        parent::__construct();
        $this->thread = $thread;

        $this->actionType = $actionType;

        $this->actionValue = $actionValue;

        $this->context = $context;

        $this->subscribers = $subscribers;
    }

    public function handle()
    {
        $thread = $this->thread;

        $actionType = $this->actionType;

        $actionValue = $this->actionValue;

        if (null === $thread) {
            return null;
        }

        $user = $this->context;

        $subscribes = $this->subscribers;

        if (null === $subscribes) {
            $subscribes = $thread->subscribes()->get();
        }

        if (null !== $subscribes) {
            foreach ($subscribes as $subscribe) {
                $subscribedUser = $subscribe->user;

                if ($user->entityId() != $subscribedUser->entityId()) {
                    switch ($actionType) {
                        case 'merge':
                            $oldTitle     = Arr::get($actionValue, 'old_title');
                            $notification = new MergeThread($thread);
                            $notification->setOldTitle($oldTitle);
                            break;
                        default:
                            $notification = new ThreadNotification($thread);
                            $notification->setActionType($actionType)
                                ->setActionValue($actionValue);
                            break;
                    }

                    $response = [$subscribedUser, $notification];

                    Notification::send(...$response);
                }
            }
        }
    }
}
