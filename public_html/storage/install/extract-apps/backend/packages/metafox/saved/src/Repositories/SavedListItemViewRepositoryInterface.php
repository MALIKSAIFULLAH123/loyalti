<?php

namespace MetaFox\Saved\Repositories;

use MetaFox\Platform\Contracts\User;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedListItemView;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SavedListItemView.
 *
 * @mixin BaseRepository
 * @method SavedListItemView find($id, $columns = ['*'])
 * @method SavedListItemView getModel()
 *                                                       stub: /packages/repositories/interface.stub
 */
interface SavedListItemViewRepositoryInterface
{
    /**
     * @param  User  $context
     * @param  array $attributes
     * @return Saved
     */
    public function markAsOpened(User $context, array $attributes): Saved;

    /**
     * @param  User  $context
     * @param  array $attributes
     * @return Saved
     */
    public function markAsUnOpened(User $context, array $attributes): Saved;

    /**
     * @param  User  $context
     * @param  array $attributes
     * @return bool
     */
    public function isExists(User $context, array $attributes): bool;
}
