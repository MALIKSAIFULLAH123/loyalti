<?php

namespace MetaFox\Story\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Models\Mute;
use MetaFox\Story\Models\Story;
use MetaFox\User\Traits\UserMorphTrait;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface MuteRepositoryInterface
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method Mute find($id, $columns = ['*'])
 * @method Mute getModel()
 * @mixin UserMorphTrait
 */
interface MuteRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Story
     */
    public function mute(User $context, array $attributes): Mute;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return bool
     */
    public function unmute(User $context, array $attributes): bool;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Builder
     */
    public function viewMuted(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function deleteMuted(User $context, int $id): bool;

    /**
     * @param User $context
     * @param int  $ownerId
     *
     * @return bool
     */
    public function isMuted(User $context, int $ownerId): bool;

    /**
     * @param User $context
     *
     * @return Builder
     */
    public function getUserMutedBuilder(User $context, array $attributes): Builder;
}
