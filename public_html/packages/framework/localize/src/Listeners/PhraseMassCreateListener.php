<?php

namespace MetaFox\Localize\Listeners;

use Illuminate\Validation\ValidationException;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

class PhraseMassCreateListener
{
    /**
     * @param  array<string, mixed> $data
     * @return void
     * @throws ValidationException
     */
    public function handle(array $data = []): void
    {
        if (empty($data)) {
            return;
        }

        $phraseRepository = resolve(PhraseRepositoryInterface::class);
        foreach ($data as $phraseData) {
            if (!is_array($phraseData)) {
                continue;
            }

            $phraseRepository->createPhrase($phraseData);
        }
    }
}
