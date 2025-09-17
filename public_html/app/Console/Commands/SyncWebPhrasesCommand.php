<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Localize\Support\PackageTranslationExporter;
use MetaFox\Platform\PackageManager;

class SyncWebPhrasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:sync-web-phrases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync web phrases then add to packages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $frontEndRoot = config('app.mfox_frontend_root');

        if (!$frontEndRoot) {
            $this->error('Could not found frontend root MFOX_FRONTEND_ROOT');

            return 1;
        }

        $this->info(sprintf('Scan frontend messages in %s', $frontEndRoot));

        foreach (PackageManager::getPackageNames() as $package) {
            $this->scanWebPhrases($frontEndRoot, $package);
        }

        return 0;
    }

    public function scanWebPhrases(string $frontEndRoot, string $package): void
    {
        $json          = PackageManager::getComposerJson($package);
        $frontendPaths = Arr::get($json, 'extra.metafox.frontendPaths');
        if (!is_array($frontendPaths)) {
            return;
        }
        foreach ($frontendPaths as $frontendPath) {
            $realpath = implode(DIRECTORY_SEPARATOR, [$frontEndRoot, $frontendPath, 'src', 'messages.json']);

            if (!file_exists($realpath)) {
                $this->error("Skip $realpath");
                continue;
            } else {
                $this->info("Found $frontendPath");
            }

            $json = json_decode(mf_get_contents($realpath), true);

            if (!is_array($json)) {
                continue;
            }

            $this->importPhrase($package, $json);
        }
    }

    public function importPhrase(string $package, array $json): void
    {
        $namespace = PackageManager::getAlias($package);

        $this->comment(sprintf('namespace %s ------ ', $namespace));

        $repository = resolve(PhraseRepositoryInterface::class);
        $counter    = 0;

        foreach ($json as $name => $text) {
            $key = sprintf('%s::web.%s', $namespace, $name);
            if ($repository->addSamplePhrase($key, $text, false)) {
                $this->comment(sprintf('Update phrase %s=%s', $name, $text));
                $counter++;
            }
        }

        if ($counter > 0) {
            $this->info(sprintf('Updated %s phrases', $counter));
        }

        resolve(PackageTranslationExporter::class)->exportTranslations($package);
    }
}
