<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Console;

use MetaFox\Platform\PackageManager;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Trait PackageScriptTrait.
 * @pckage MetaFox\Platform\Console
 * @method void executeOne(string $packageName)
 */
trait PackageScriptTrait
{
    /**
     * Get the console command arguments.
     *
     * @return array<mixed>
     */
    protected function getArguments(): array
    {
        return [
            ['package', InputArgument::OPTIONAL, 'The name of package will be used.'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $packageName = $this->argument('package');

        if (is_string($packageName)) {
            $this->executeOne($packageName);

            return 0;
        }

        $this->executeAll();

        return 0;
    }

    /**
     * Publish assets from all packages.
     */
    public function executeAll(): void
    {
        foreach (PackageManager::getPackageNames() as $packageName) {
            if (is_string($packageName)) {
                $this->executeOne($packageName);
            }
        }
    }
}
