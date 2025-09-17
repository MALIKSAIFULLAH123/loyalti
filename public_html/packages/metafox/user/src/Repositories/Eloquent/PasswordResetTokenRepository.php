<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Repositories\PasswordResetTokenRepositoryInterface;
use MetaFox\User\Models\PasswordResetToken as Model;

/**
 * Class PasswordResetTokenRepository.
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 */
class PasswordResetTokenRepository extends AbstractRepository implements PasswordResetTokenRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function createToken(User $user, array $attributes = []): Model
    {
        $asNumber = Arr::get($attributes, 'as_numeric', false);
        $value    = $asNumber ? random_int(100000, 999999) : Str::random(100);
        $params   = [
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityId(),
            'value'      => $value,
            'expired_at' => Carbon::now()->addMinutes(5),
        ];

        $token = $this->getModel()->newModelQuery()
            ->where('user_id', '=', $user->entityId())
            ->where('value', '=', $value)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if ($token instanceof Model) {
            return $token;
        }

        $token = new Model();
        $token->fill($params);
        $token->save();

        return $token;
    }

    /**
     * @param  User   $user
     * @param  string $token
     * @return bool
     */
    public function verifyToken(User $user, string $token): bool
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('value', '=', $token)
            ->where('user_id', '=', $user->entityId())
            ->where('expired_at', '>', Carbon::now())
            ->exists();
    }

    public function flushTokens(User $user): void
    {
        $this->getModel()
            ->newModelQuery()
            ->where('user_id', '=', $user->entityId())
            ->update(['expired_at' => Carbon::now()->subHours(3)]);
    }
}
