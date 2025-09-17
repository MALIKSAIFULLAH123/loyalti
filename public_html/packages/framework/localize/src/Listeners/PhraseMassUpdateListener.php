<?php

namespace MetaFox\Localize\Listeners;

use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

class PhraseMassUpdateListener
{
    /**
     * @param  array<string, mixed> $data
     * @return void
     */
    public function handle(array $data = []): void
    {
        if (empty($data)) {
            return;
        }

        $phraseRepository = resolve(PhraseRepositoryInterface::class);
        foreach ($data as $params) {
            if (!is_array($params)) {
                continue;
            }

            $phraseRepository->updatePhraseByKey(...$params);
        }
    }
}
