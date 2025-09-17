<?php

namespace MetaFox\TourGuide\Observers;

use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\TourGuide\Models\Step;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class StepObserver.
 */
class StepObserver
{
    public function deleted(Step $model): void
    {
        $this->getPhraseRepository()->deleteWhere(['key' => $model->title_var]);
        $this->getPhraseRepository()->deleteWhere(['key' => $model->desc_var]);
    }

    protected function getPhraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }
}
