<?php

namespace MetaFox\Translation\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Translation\Repositories\TranslationServiceRepositoryInterface;
use MetaFox\Translation\Repositories\TranslationTextRepositoryInterface;

class TranslatingListener
{
    public function __construct(
        public TranslationServiceRepositoryInterface $translationServiceRepository,
        public TranslationTextRepositoryInterface $translationTextRepository
    ) {}

    public function handle(string $text, Content $content, User $user, array $parameters = [])
    {
        if (empty($parameters['target'])) {
            $parameters['target'] = $user->profile?->language_id ?? config('app.locale');
        }

        $translatedText = $this->translationTextRepository->getTranslatedText([
            'entity_id'   => $content->entityId(),
            'entity_type' => $content->entityType(),
            'language_id' => $parameters['target'],
        ]);

        if ($translatedText) {
            return [
                'original_text'   => $text,
                'translated_text' => $translatedText->text,
                'target'          => $parameters['target'],
            ];
        }

        $data = $this->translationServiceRepository->translate($text, $parameters);

        if (count($data) > 0 && isset($data['translated_text'])) {
            $this->translationTextRepository->createTranslationText([
                'entity_id'   => $content->entityId(),
                'entity_type' => $content->entityType(),
                'language_id' => $parameters['target'],
                'text'        => $data['translated_text'],
            ]);

            return [
                'original_text'   => $text,
                'translated_text' => $data['translated_text'],
                'target'          => $parameters['target'],
            ];
        }

        return [];
    }
}
