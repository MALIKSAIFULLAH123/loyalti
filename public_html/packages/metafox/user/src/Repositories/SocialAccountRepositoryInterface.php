<?php

namespace MetaFox\User\Repositories;

use Laravel\Socialite\Two\User as SocialUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\SocialAccount;
use MetaFox\User\Models\User;

/**
 * Interface SocialAccountRepositoryInterface.
 * @mixin AbstractRepository
 */
interface SocialAccountRepositoryInterface
{
    /**
     * Check if social account exists.
     *
     * @param string       $providerUserId
     * @param string       $provider
     * @param array<mixed> $with           - relations.
     *
     * @return SocialAccount|null
     */
    public function findSocialAccount(string $providerUserId, string $provider, array $with = []): ?SocialAccount;

    /**
     * Create social account.
     *
     * @param SocialUser $socialUser
     * @param string     $provider
     * @param array      $params
     *
     * @return SocialAccount
     */
    public function createSocialAccount(SocialUser $socialUser, string $provider, array $params): SocialAccount;

    /**
     * @param  SocialUser    $socialUser
     * @param  string        $providerName
     * @param  array         $params
     * @return SocialAccount
     */
    public function findOrCreateSocialAccount(SocialUser $socialUser, string $providerName, array $params): SocialAccount;

    /**
     * @param  SocialAccount $socialAccount
     * @return User
     */
    public function handleUserAccount(SocialAccount $socialAccount): User;

    /**
     * @param  string        $hash
     * @return SocialAccount
     */
    public function findSocialAccountByHash(string $hash): SocialAccount;

    /**
     * @param int $userId
     *
     * @return void
     */
    public function deleteSocialAccountsByUserId(int $userId);

    /**
     * @param  SocialAccount $socialAccount
     * @return SocialAccount
     */
    public function assignUserToSocialAccount(SocialAccount $socialAccount): SocialAccount;

    /**
     * @param  SocialAccount $socialAccount
     * @return array
     */
    public function processUserSignIn(SocialAccount $socialAccount): array;

    public function isRequiredVerifyInviteCode(bool $checkUserExisted = true, string $socialEmail = ''): bool;
}
