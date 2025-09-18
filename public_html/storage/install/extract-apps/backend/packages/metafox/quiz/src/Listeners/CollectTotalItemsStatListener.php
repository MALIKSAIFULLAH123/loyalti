<?php

namespace MetaFox\Quiz\Listeners;

use Carbon\Carbon;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Core\Listeners\Abstracts\AbstractCollectTotalItemStatListener as AbstractClass;

class CollectTotalItemsStatListener extends AbstractClass
{
    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => Quiz::ENTITY_TYPE,
                'label' => 'quiz::phrase.quiz_stat_label',
                'value' => resolve(QuizRepositoryInterface::class)->getTotalItemByPeriod($after, $before),
            ],
        ];
    }

    /**
     * @param  Carbon|null            $after
     * @param  Carbon|null            $before
     * @return array<int, mixed>|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPendingStats(?Carbon $after = null, ?Carbon $before = null): ?array
    {
        return [
            [
                'name'  => 'pending_quiz',
                'label' => 'quiz::phrase.quiz_stat_label',
                'value' => resolve(QuizRepositoryInterface::class)->getTotalPendingItemByPeriod(),
            ],
        ];
    }
}
