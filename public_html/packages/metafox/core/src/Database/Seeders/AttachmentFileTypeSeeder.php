<?php

namespace MetaFox\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Core\Models\AttachmentFileType as Type;

/**
 * Class AttachmentFileTypeSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class AttachmentFileTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Type::query()->exists()) {
            return;
        }

        Type::query()->insert([
            [
                'extension' => 'gif',
                'mime_type' => 'image/gif',
                'is_active' => 1,
            ],
            [
                'extension' => 'jpeg',
                'mime_type' => 'image/jpeg',
                'is_active' => 1,
            ],
            [
                'extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'is_active' => 1,
            ],
            [
                'extension' => 'png',
                'mime_type' => 'image/png',
                'is_active' => 1,
            ],
            [
                'extension' => 'zip',
                'mime_type' => 'application/zip',
                'is_active' => 1,
            ],
        ]);
    }
}
