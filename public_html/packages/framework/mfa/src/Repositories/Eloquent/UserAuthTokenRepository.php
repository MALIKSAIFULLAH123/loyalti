<?php

namespace MetaFox\Mfa\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Str;
use MetaFox\Mfa\Models\UserAuthToken;
use MetaFox\Mfa\Repositories\UserAuthTokenRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class UserAuthTokenRepository.
 */
class UserAuthTokenRepository extends AbstractRepository implements UserAuthTokenRepositoryInterface
{
    public function model()
    {
        return UserAuthToken::class;
    }

    public function generateTokenForUser(User $user, int $lifetime = 5, string $resolution = MetaFoxConstant::RESOLUTION_WEB): UserAuthToken
    {
        /** @var UserAuthToken $userAuthToken */
        $userAuthToken = $this->getModel()->fill([
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'value'      => $this->generateTokenValue(),
            'expired_at' => Carbon::now()->addMinutes($lifetime),
            'resolution' => $resolution,
        ]);

        $userAuthToken->save();

        return $userAuthToken;
    }

    public function findByTokenValue(string $mfaToken): ?UserAuthToken
    {
        return $this->getModel()->where('value', $mfaToken)->first();
    }

    private function generateTokenValue(): string
    {
        do {
            $token = Str::random(100);
        } while ($this->findByTokenValue($token));

        return $token;
    }

    public function deleteTokensByUserId(int $userId)
    {
        $this->deleteWhere([
            'user_id' => $userId,
        ]);
    }
}
