<?php

namespace MetaFox\Activity\Observers;

use MetaFox\Activity\Models\Post;
use MetaFox\User\Models\User;

/**
 * Class PostObserver.
 */
class PostObserver
{
    public function created(Post $model): void
    {
        $this->increaseUserActivityPoints($model);
    }

    public function deleted(Post $model): void
    {
        $this->decreaseUserActivityPoints($model);
    }

    protected function increaseUserActivityPoints(Post $model): bool
    {
        if (!$model->owner instanceof User) {
            return false;
        }

        $action = $model->userId() == $model->ownerId() ? 'post_on_wall' : 'post_on_other';

        return $this->increaseUserPoints($model, $action);
    }

    protected function decreaseUserActivityPoints(Post $model): bool
    {
        if (!$model->owner instanceof User) {
            return false;
        }

        $action = $model->userId() == $model->ownerId() ? 'post_on_wall' : 'post_on_other';

        return $this->decreaseUserPoints($model, $action);
    }

    protected function increaseUserPoints(Post $model, string $action): bool
    {
        app('events')->dispatch('activitypoint.increase_user_point', [$model->user, $model, $action]);

        return true;
    }

    protected function decreaseUserPoints(Post $model, string $action): bool
    {
        app('events')->dispatch('activitypoint.decrease_user_point', [$model->user, $model, $action]);

        return true;
    }
}

// end stub
