<?php

namespace MetaFox\Mfa\Repositories;

use MetaFox\Mfa\Models\UserAuthToken;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface UserAuthToken.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface UserAuthTokenRepositoryInterface
{
    /**
     * generateTokenForUser.
     *
     * @param User $user
     * @param int  $lifetime
     *
     * @return UserAuthToken
     */
    public function generateTokenForUser(User $user, int $lifetime, string $resolution = MetaFoxConstant::RESOLUTION_WEB): UserAuthToken;

    /**
     * findByTokenValue.
     *
     * @param string $mfaToken
     *
     * @return UserAuthToken
     */
    public function findByTokenValue(string $mfaToken): ?UserAuthToken;

    /**
     * @param int $userId
     *
     * @return void
     */
    public function deleteTokensByUserId(int $userId);
}
