<?php

namespace MetaFox\Localize\Listeners;

use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

class PhraseMassDeleteListener
{
    /**
     * @param  array<string> $phraseKeys
     * @return void
     */
    public function handle(array $phraseKeys = []): void
    {
        if (empty($phraseKeys)) {
            return;
        }

        $phraseService = resolve(PhraseRepositoryInterface::class);
        foreach ($phraseKeys as $key) {
            $phraseService->deletePhraseByKey($key);
        }
    }
}
