<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Advertise\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

/**
 * Class ModelDeletedListener.
 * @ignore
 */
class ModelDeletedListener
{
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        resolve(SponsorRepositoryInterface::class)->deleteDataByItem($model);
    }
}
