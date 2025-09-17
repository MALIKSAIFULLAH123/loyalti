<?php

namespace MetaFox\Translation\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Translation\Models\TranslationText;
use MetaFox\Translation\Repositories\TranslationTextRepositoryInterface;

/**
 * Class TranslationTextRepository.
 * @method TranslationText getModel()
 * @method TranslationText find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class TranslationTextRepository extends AbstractRepository implements TranslationTextRepositoryInterface
{
    public function model()
    {
        return TranslationText::class;
    }

    public function getTranslatedText(array $attributes)
    {
        return $this->getModel()->newQuery()
            ->where('entity_id', $attributes['entity_id'])
            ->where('entity_type', $attributes['entity_type'])
            ->where('language_id', $attributes['language_id'])
            ->first();
    }

    public function createTranslationText(array $attributes): TranslationText
    {
        $translationText = new TranslationText($attributes);
        $translationText->save();
        $translationText->refresh();

        return $translationText;
    }

    public function deleteByItem(int $entityId, string $entityType)
    {
        $this->getModel()->newQuery()
            ->where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->delete();
    }
}
