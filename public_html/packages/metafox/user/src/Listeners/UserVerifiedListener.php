<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Facades\Notification;
use MetaFox\User\Jobs\UserPendingApprovalJob;
use MetaFox\User\Models\User;
use MetaFox\User\Notifications\WelcomeNewMember;

class UserVerifiedListener
{
    public function handle(User $user)
    {
        $this->handleWelcomeEmail($user);
        $this->handleSendNotifyPending($user);
    }

    private function handleSendNotifyPending(User $user): void
    {
        if (!$user->hasVerified()) {
            return;
        }

        if ($user->isPendingApproval()) {
            UserPendingApprovalJob::dispatch($user->entityId());
        }
    }

    private function handleWelcomeEmail(User $user)
    {
        if (!$user->hasVerified()) {
            return;
        }

        Notification::send($user, new WelcomeNewMember($user));
    }
}
