<?php

namespace MetaFox\Core\Repositories;

use MetaFox\Core\Models\ItemStatistics;
use MetaFox\Platform\Contracts\Content;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface CommentPendingStatistics.
 * @mixin BaseRepository
 * @method ItemStatistics getModel()
 * @method ItemStatistics find($id, $columns = ['*'])
 */
interface ItemStatisticsRepositoryInterface
{
    /**
     * @param  ?Content $item
     * @param  string   $column
     * @return void
     */
    public function increaseTotal(?Content $item, string $column): void;

    /**
     * @param  ?Content $item
     * @param  string   $column
     * @return void
     */
    public function decreaseTotal(?Content $item, string $column): void;
}
