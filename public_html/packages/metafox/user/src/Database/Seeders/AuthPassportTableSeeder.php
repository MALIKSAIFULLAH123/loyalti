<?php

namespace MetaFox\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

/**
 * Class AuthPassportTableSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class AuthPassportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provider = in_array('users', array_keys(Config::get('auth.providers'))) ? 'users' : null;
        Artisan::call('passport:keys');
        Artisan::call(
            'passport:client',
            ['--personal' => true, '--name' => config('app.name') . ' Personal Access Client']
        );
        Artisan::call(
            'passport:client',
            ['--password' => true, '--name' => config('app.name') . ' Password Grant Client', '--provider' => $provider]
        );
    }
}
