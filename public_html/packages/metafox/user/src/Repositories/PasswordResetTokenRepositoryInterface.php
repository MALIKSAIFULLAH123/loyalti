<?php

namespace MetaFox\User\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\PasswordResetToken as Model;

/**
 * Interface PasswordResetToken.
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 * @mixin Builder
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface PasswordResetTokenRepositoryInterface
{
    /**
     * @param  User                 $user
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function createToken(User $user, array $attributes = []): Model;

    /**
     * @param User   $user
     * @param string $token
     */
    public function verifyToken(User $user, string $token): bool;

    /**
     * @param User $user
     */
    public function flushTokens(User $user): void;
}
