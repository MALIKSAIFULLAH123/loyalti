<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;

/**
 * Class InstallPlatformCommand.
 * @codeCoverageIgnore
 */
class PlatformInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:install {--force} {--log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MetaFox installation command.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '-1');
        }

        if (!file_exists(base_path('.env'))) {
            $this->error('.env file does not exists');

            return 1;
        }

        $force = (bool) $this->option('force');

        $platformInstalled = config('app.mfox_installed');
        if ($platformInstalled) {
            $this->error('The platform has been already installed. Please check the MFOX_APP_INSTALLED variable.');

            return 1;
        }

        if (!Schema::hasTable('packages')) {
            $this->info('Fresh install');
            $force = true;
        }

        if (!$force) {
            $this->info('run `php artisan db:wipe --force` to clear database first!');

            return 1;
        }

        $this->call('db:wipe', ['--force' => true]);

        Artisan::call('optimize:clear');

        $databaseEngine = DB::getDefaultConnection();
        $this->info("Using Database Engine: {$databaseEngine}.");

        // if ($databaseEngine == 'pgsql') {
        //      DB::statement('CREATE EXTENSION IF NOT EXISTS Postgis;');
        // }

        // Generate app key.
        $this->call('key:generate', ['--force' => true]);

        // Generate metadata for develop.
        $this->call('storage:link');

        // Install bsae database + seeder.
        $this->call('migrate', ['--force' => true]);

        $this->call('db:seed', ['--force' => true]);

        /*
         * Migrate data structure
         */
        $this->info('Sync database configuration');
        $collect = [];
        PackageManager::with(function ($name) use (&$collect) {
            $collect['Migrating ' . $name] = fn () => $this->callSilent('package', [
                'package'   => $name,
                '--migrate' => true,
            ]) == 0;
        });

        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));

        /*
         * Sync info to packages
         */
        $this->info('Update Package Information');
        $collect = [];
        PackageManager::with(function ($name) use (&$collect) {
            $collect['Sync ' . $name] = fn () => $this->callSilent('package', [
                'package' => $name,
                '--sync'  => true,
            ]) == 0;
        });
        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));

        /*
         * Seeding to database
         */
        $this->info('Process seeding');
        $collect = [];
        PackageManager::with(function ($name) use (&$collect) {
            $collect['Seeding ' . $name] = fn () => $this->callSilent('package', [
                'package' => $name,
                '--seed'  => true,
            ]) == 0;
        });
        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));

        /*
         * Seeding to database
         */
        $this->info('Setup menus, activity, notifications, settings, permissions ...');
        $collect = [];
        PackageManager::with(function ($name) use (&$collect) {
            $collect['Setup ' . $name] = fn () => $this->callSilent('package', [
                'package'    => $name,
                '--dispatch' => true,
            ]) == 0;
        });
        collect($collect)->each(fn ($task, $description) => $this->components->task($description, $task));
        Artisan::call('config:clear');

        $this->runRequired();
        $this->markMetaFoxPlatformInstalled();

        $this->call('config:clear');

        Log::channel('installation')->debug(__METHOD__ . ' sucessfully.');

        return 0;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function runRequired(): int
    {
        // Keep the same API key + secret.
        $key    = config('app.api_key');
        $secret = config('app.api_secret');
        DB::table('oauth_clients')->where('id', '=', $key)->update([
            'secret' => $secret,
        ]);

        $this->info("API Key: {$key}");
        $this->info("API Secret: {$secret}");

        Artisan::call('optimize:clear');

        $this->info('Create site super administrator');

        $this->createUser(
            config('app.site_username'),
            config('app.site_password'),
            config('app.site_email'),
            config('app.name'),
            UserRole::SUPER_ADMIN_USER
        );

        // Clear cache again.
        $this->call('optimize:clear');

        return 0;
    }

    private function createUser(string $userName, string $password, string $email, string $fullName, string $role): void
    {
        $user = User::query()->where('user_name', '=', $userName)->first();
        if (!$user) {
            $user = User::factory()
                ->asSuperAdmin($userName, $password, $email, $fullName)
                ->create();
            $user->assignRole($role);
        }
    }

    /**
     * Write a new environment file with the given key.
     *
     * @return void
     */
    protected function markMetaFoxPlatformInstalled()
    {
        $filename = $this->laravel->environmentFilePath();
        $content  = mf_get_contents($this->laravel->environmentFilePath());
        $pattern  = '/^MFOX_APP_INSTALLED=(.*)$/m';
        $need     = 'MFOX_APP_INSTALLED=true';

        if (preg_match($pattern, $content)) {
            $content = preg_replace(
                $pattern,
                $need,
                $content,
            );
        } else {
            $content = $content . PHP_EOL . $need . PHP_EOL;
        }

        file_put_contents($filename, $content);
    }
}
