<?php

namespace MetaFox\Core\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Class PackageSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(PrivacyTableSeeder::class);
        $this->call(SiteSettingSeeder::class);
        $this->call(AttachmentFileTypeSeeder::class);
    }
}
