<?php

namespace MetaFox\BackgroundStatus\Repositories;

use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface BgsBackgroundRepositoryInterface.
 * @mixin BaseRepository
 * @method BgsCollection getModel()
 * @method BgsCollection find($id, $columns = ['*'])
 */
interface BgsBackgroundRepositoryInterface
{
    /**
     * @param  User          $context
     * @param  BgsCollection $bgsCollection
     * @param  array         $attributes
     * @return void
     */
    public function uploadBackgrounds(User $context, BgsCollection $bgsCollection, array $attributes): void;
}
