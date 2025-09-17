<?php

namespace MetaFox\Forum\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use MetaFox\Forum\Notifications\CopyThread as CopyNotification;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;

class CopyThread extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var User
     */
    protected $context;

    /**
     * @var array
     */
    protected $attributes;

    public function __construct(User $context, array $attributes)
    {
        parent::__construct();
        $this->context = $context;

        $this->attributes = $attributes;
    }

    public function handle()
    {
        try {
            $thread = resolve(ForumThreadRepositoryInterface::class)->copy($this->context, $this->attributes);
        } catch (Exception $exception) {
            $thread = null;
        }

        if (null === $thread) {
            return null;
        }

        $notification = [$this->context, new CopyNotification($thread)];

        Notification::send(...$notification);
    }
}
