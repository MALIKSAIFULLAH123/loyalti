<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Search\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

/**
 * Class ModelCreatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelCreatedListener
{
    /**
     * @param Model $model
     */
    public function handle($model): void
    {
        if ($model instanceof HasGlobalSearch) {
            resolve(SearchRepositoryInterface::class)->createdBy($model);
        }
    }
}
