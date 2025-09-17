<?php

namespace MetaFox\Storage\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Class PackageSeeder.
 * @ignore
 * @codeCoverageIgnore
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
        app('storage')->removeTap('web');

        app('storage')->tap('default', [
            'label'     => 'Default',
            'is_system' => true,
        ]);

        app('storage')
            ->tap('asset', [
                'label'     => 'Application Assets (image, css, ...etc)',
                'is_system' => true,
            ]);
        app('storage')
            ->tap('bundle', [
                'label'     => 'Frontend Bundle',
                'target'    => 'web',
                'is_system' => true,
            ]);
        app('storage')
            ->tap('photo', [
                'label'     => 'Photos',
                'is_system' => true,
            ]);
        app('storage')
            ->tap('video', [
                'label'     => 'Videos',
                'is_system' => true,
            ]);
        app('storage')
            ->tap('attachment', [
                'label'     => 'Attachments',
                'is_system' => true,
            ]);
        app('storage')
            ->tap('temp', [
                'label'     => 'Temporary',
                'is_system' => true,
            ]);
    }
}
