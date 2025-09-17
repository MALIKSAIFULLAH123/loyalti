<?php

namespace MetaFox\Translation\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Translation\Repositories\TranslationTextRepositoryInterface;

class ModelDeletedListener
{
    public function __construct(protected TranslationTextRepositoryInterface $translationTextRepository)
    {
    }

    public function handle(Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        $this->translationTextRepository->deleteByItem($model->entityId(), $model->entityType());
    }
}
