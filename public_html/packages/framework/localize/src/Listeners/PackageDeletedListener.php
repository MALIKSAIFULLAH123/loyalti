<?php

namespace MetaFox\Localize\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;

/**
 * Class PackageDeletedListener.
 * Handle event "packages.deleted".
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageDeletedListener
{
    /**
     * @param string $package
     */
    public function handle(string $package): void
    {
        try {
            $this->cleanupPhrases($package);
            $this->cleanupLanguage($package);
        } catch (Exception $exception) {
            Log::channel('installation')->error($exception->getMessage());
        }
    }

    private function cleanupPhrases(string $package): void
    {
        Log::channel('installation')->info(sprintf('cleanupPhrases  "%s"', $package));
        try {
            resolve(PhraseRepositoryInterface::class)->getModel()
                ->newQuery()
                ->where(['package_id' => $package])
                ->delete();
        } catch (Exception $exception) {
            Log::channel('installation')->error($exception->getMessage());
        }
    }

    private function cleanupLanguage(string $package): void
    {
        Log::channel('installation')->info(sprintf('cleanupLanguage  "%s"', $package));
        // TODO: multiple package for language supports.
    }
}
