<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Trait PackageCommandTrait.
 *
 * @mixin Command
 * @property array $transforms
 */
trait FileTranslateTrait
{
    /**
     * Execute the console command.
     *
     * @param string $saveTo
     * @param string $stub
     * @param array  $replacements
     * @param bool   $overwriteFile
     * @param bool   $silent
     *
     * @return int
     */
    public function translate(
        string $saveTo,
        string $stub,
        array $replacements,
        bool $overwriteFile = false,
        bool $silent = true
    ): int {
        $path = str_replace(
            '\\',
            '/',
            base_path($this->translateSavedToPath($saveTo, $replacements))
        );

        if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        Stub::setBasePath(app_path('Console/Commands/stubs/'));

        $replacements['STUB'] = $stub;
        $replacements['UPDATE_AT'] = Carbon::now()->toString();

        $contents = (new Stub($stub, $replacements))->render();

        try {
            (new FileGenerator($path, $contents))
                ->withFileOverwrite($overwriteFile)->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException) {
            if (!$silent) {
                $this->error("File : {$path} already exists.");
            } else {
                $this->comment("File : {$path} already exists.");
            }

            return E_ERROR;
        }

        return 0;
    }

    public function translateSavedToPath(string $path, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $path = str_replace('$' . strtoupper($search) . '$', $replace, $path);
        }

        return $path;
    }
}
