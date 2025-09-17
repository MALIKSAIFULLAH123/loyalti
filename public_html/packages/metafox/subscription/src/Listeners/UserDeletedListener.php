<?php
namespace MetaFox\Subscription\Listeners;

use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        try {
            resolve(SubscriptionInvoiceRepositoryInterface::class)->cancelAllUserActiveSubscriptions($user);
        } catch (\Throwable $exception) {
            Log::info('error message when delete subscriptions when user cancel account: ' . $exception->getMessage());
        }
    }
}
