<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Events\AccessTokenCreated;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Support\Facades\User;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AccessTokenCreatedListener
{
    /**
     * Handle the event.
     *
     * @param AccessTokenCreated $event
     *
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        Cache::flush();
        /** @var UserModel $context */
        $context = UserModel::query()->find($event->userId);
        User::updateLastLogin($context);

        app('events')->dispatch('user.login', [$context]);
    }
}
