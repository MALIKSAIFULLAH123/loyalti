<?php

namespace MetaFox\Localize\Observers;

use Exception;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Localize\Models\Language;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

/**
 * Class LanguageObserver.
 */
class LanguageObserver
{
    /**
     * @throws Exception
     */
    public function deleted(Language $language): void
    {
        $this->uninstallPackage($language->package_id);
        $this->removePhrasesByLocale($language->language_code);
    }

    protected function uninstallPackage(?string $packageId): void
    {
        if (!is_string($packageId)) {
            return;
        }

        Artisan::call('package:uninstall', [
            'package' => $packageId,
            '--clean' => true,
        ]);
    }

    protected function removePhrasesByLocale(string $locale): void
    {
        resolve(PhraseRepositoryInterface::class)->deletePhrasesByLocale($locale);
    }
}
