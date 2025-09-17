<?php

namespace App\Console\Commands;

class MakeLanguageCommand extends MakePackageCommand
{
    protected $signature = 'package:make-language {package}
    {--vendor= : Vendor Name}
    {--name= : Package Name}
    {--author= : Author Name}
    {--homepage= : Homepage Url}
    {--title= : Language Title}
    {--direction= : Direction}
    {--language_code= : Language Name}
    {--base_language= : Base Language}
    {--dry : Dry run}
    {--test : Dry run}
    {--type=language : Package Type}
    {--overwrite : Overwrite current}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new translation pack.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $package = $this->argument('package');

        $this->packagePath = implode(DIRECTORY_SEPARATOR, [
            'packages',
            $package,
        ]);

        $this->generateFolders();

        $this->generateFiles();

        $name = (string) $this->option('language_code') . '.csv';

        $path = base_path(implode(DIRECTORY_SEPARATOR, [$this->getPackagePath(), 'resources/lang/' . $name]));

        resolve('translation')
            ->exportTranslationsCSV(
                $path,
                (string) $this->option('language_code')
            );

        return 0;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getReplacements(): array
    {
        $data = parent::getReplacements();

        $data['DIRECTION']     = $this->option('direction');
        $data['TITLE']         = $this->option('title');
        $data['AUTHOR_NAME']   = $this->option('author');
        $data['AUTHOR_URL']    = $this->option('homepage');
        $data['LANGUAGE_CODE'] = $this->option('language_code');
        $data['CHARSET']       = 'utf8';

        return $data;
    }

    /**
     * Get the list of folders.
     *
     * @return array<mixed>
     */
    protected function getFolders(): array
    {
        return [
            'src/Providers',
            'config',
            'resources/lang/' . $this->option('language_code'),
        ];
    }

    /**
     * Get the list of files created.
     *
     * @return array<mixed>
     */
    protected function getFiles(): array
    {
        return [
            'packages/config/config'      => 'config/config.php',
            'lang-composer'               => 'composer.json',
            'scaffold/listener_settings'  => 'src/Listeners/PackageSettingListener.php',
            'packages/providers/provider' => 'src/Providers/PackageServiceProvider.php',
        ];
    }
}
