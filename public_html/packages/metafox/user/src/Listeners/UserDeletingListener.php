<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\SocialAccountRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class UserDeletingListener
{
    public function __construct(
        protected SocialAccountRepositoryInterface $socialAccountRepository
    ) {
    }

    /**
     * @param mixed $user
     *
     * @return void
     */
    public function handle($user): void
    {
        if (!$user instanceof User) {
            return;
        }
        $timestamp        = Carbon::now()->timestamp;
        $uniqueAttributes = [
            'email'        => md5(Str::random(32) . $timestamp),
            'user_name'    => md5(Str::random(32) . $timestamp),
            'phone_number' => null,
        ];

        $user->update($uniqueAttributes);
        $this->handleSocialAccounts($user);
        $this->revokeAllTokens($user);
    }

    /**
     * @param User $user
     *
     * @return void
     */
    protected function handleSocialAccounts(User $user)
    {
        $this->socialAccountRepository->deleteSocialAccountsByUserId($user->entityId());
    }

    protected function revokeAllTokens(User $user): void
    {
        $user->revokeAllTokens();
    }
}
