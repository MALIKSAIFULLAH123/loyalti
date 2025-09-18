<?php

namespace MetaFox\Page\Repositories;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageHistory;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface PageHistory
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 * @method PageHistory getModel()
 * @method PageHistory find($id, $columns = ['*'])
 */
interface PageHistoryRepositoryInterface
{
    /**
     * @param User  $context
     * @param Page  $page
     * @param array $attributes
     * @return void
     */
    public function createHistory(User $context, Page $page, array $attributes): void;

    /**
     * @param PageHistory $model
     * @return void
     */
    public function sentNotification(PageHistory $model): void;
}
