<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\UserRole;
use MetaFox\User\Database\Factories\UserProfileFactory;
use MetaFox\User\Models\User;

class MetaFoxSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metafox:seed {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MetaFox seed database command.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
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
            'Admin',
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
                ->has(UserProfileFactory::new(), 'profile')
                ->asSuperAdmin($userName, $password, $email, $fullName)
                ->create();
            $user->assignRole($role);
        }
    }
}
