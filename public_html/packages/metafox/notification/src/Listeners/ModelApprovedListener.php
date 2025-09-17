<?php

namespace MetaFox\Notification\Listeners;

use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Notifications\ApproveNotification;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    /**
     * @param User|null $context
     * @param Model     $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        if (!$context instanceof User) {
            return;
        }

        if ($context->entityId() == $model->userId()) {
            return;
        }

        if (!method_exists($model, 'toApprovedNotification')) {
            return;
        }

        [$users, $module] = $model->toApprovedNotification();
        if ($module instanceof ApproveNotification) {
            $module->setContext($context);
            $response = [$users, $module];

            Notification::send(...$response);
        }
    }
}
