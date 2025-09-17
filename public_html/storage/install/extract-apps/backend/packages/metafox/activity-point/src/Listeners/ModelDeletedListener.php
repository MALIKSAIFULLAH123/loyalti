<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Listeners;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelDeletedListener.
 * @ignore
 */
class ModelDeletedListener
{
    /**
     * @param Model $model
     */
    public function handle($model): void
    {
    }
}
