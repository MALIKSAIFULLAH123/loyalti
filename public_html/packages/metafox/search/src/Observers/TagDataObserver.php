<?php

namespace MetaFox\Search\Observers;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Search\Repositories\HashtagStatisticRepositoryInterface;

class TagDataObserver
{
    public function created(Model $model)
    {
        resolve(HashtagStatisticRepositoryInterface::class)->increaseTotal($model->tag_id);
    }

    public function deleted(Model $model)
    {
        resolve(HashtagStatisticRepositoryInterface::class)->decreaseTotal($model->tag_id);
    }
}
