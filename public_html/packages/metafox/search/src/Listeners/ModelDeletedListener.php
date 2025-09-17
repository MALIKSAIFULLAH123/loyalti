<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Search\Listeners;

use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

/**
 * Class ModelDeletedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelDeletedListener
{
    /**
     * @param mixed $model
     */
    public function handle($model): void
    {
        if ($model instanceof HasGlobalSearch) {
            resolve(SearchRepositoryInterface::class)->deletedBy($model);
        }
    }
}
