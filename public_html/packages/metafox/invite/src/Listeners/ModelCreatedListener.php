<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class ModelCreatedListener.
 * @ignore
 */
class ModelCreatedListener
{
    public function __construct()
    {
    }
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if (!$model instanceof Invite) {
            return;
        }

        app('events')->dispatch('activitypoint.increase_user_point', [$model->user, $model, Invite::ACTION_CREATE]);
    }
}
