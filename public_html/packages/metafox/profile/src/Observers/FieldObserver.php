<?php

namespace MetaFox\Profile\Observers;

use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Repositories\ValueRepositoryInterface;

class FieldObserver
{
    public function deleted(Field $field): void
    {
        $field?->options()?->delete();

        /**@var ValueRepositoryInterface $valueRepository */
        $valueRepository = resolve(ValueRepositoryInterface::class);
        $valueRepository->getModel()->newQuery()->where('field_id', $field->entityId())->delete();
    }
}
