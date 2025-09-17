<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\ActivityPoint\Support\ActivityPoint as PointSupport;
use MetaFox\ActivityPoint\Support\Facade\ActivityPoint;
use MetaFox\Platform\Contracts\Content;

/**
 * Class ModelCreatedListener.
 * @ignore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModelUpdatedListener
{
    /**
     * @param Model $model
     */
    public function handle($model): void
    {
        if ($model instanceof Content) {
            $this->handleUpdateActivityPoint($model);
        }
    }

    /**
     * @param Content $model
     */
    private function handleUpdateActivityPoint(Content $model): void
    {
        if (!$model instanceof Model) {
            return;
        }

        if ($model->isDraft() || !$model->isApproved()) {
            return;
        }

        if (!$model->isDirty(['is_draft', 'is_approved'])) {
            return;
        }

        ActivityPoint::updateUserPoints($model->user, $model, 'create', PointSupport::TYPE_EARNED);
    }
}
