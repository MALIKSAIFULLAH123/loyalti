<?php

namespace MetaFox\Comment\Listeners;

use Illuminate\Support\Facades\Notification;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Repositories\CommentStatisticRepositoryInterface;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Contracts\User;
use Illuminate\Database\Eloquent\Model;

class ModelApprovedListener
{
    public function __construct(protected CommentStatisticRepositoryInterface $commentStatisticRepository)
    {
    }

    /**
     * @param  User|null $context
     * @param  mixed     $model
     * @return void
     */
    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof Comment) {
            return;
        }

        $this->commentStatisticRepository->increaseTotal($model);
        $this->commentStatisticRepository->decreaseTotalPending($model);

        $this->sendNotification($model);
    }

    protected function sendNotification(Comment $comment): void
    {
        if (!$comment instanceof IsNotifyInterface) {
            return;
        }

        $response = $comment->toNotification();
        if (is_array($response)) {
            Notification::send(...$response);
        }
    }
}
