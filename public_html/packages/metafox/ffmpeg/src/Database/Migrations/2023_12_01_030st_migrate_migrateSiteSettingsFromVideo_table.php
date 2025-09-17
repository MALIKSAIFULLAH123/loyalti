<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Facades\Settings;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $oldSettings = [
            'video.ffmpeg.binaries',
            'video.ffprobe.binaries',
            'video.ffmpeg.timeout',
            'video.ffmpeg.threads',
        ];

        Settings::save([
            'ffmpeg.binaries'         => Settings::get('video.ffmpeg.binaries'),
            'ffmpeg.ffprobe_binaries' => Settings::get('video.ffprobe.binaries'),
            'ffmpeg.timeout'          => Settings::get('video.ffmpeg.timeout'),
            'ffmpeg.threads'          => Settings::get('video.ffmpeg.threads'),
        ]);
        
        Settings::destroy('video', $oldSettings);

        resolve(DriverRepositoryInterface::class)
            ->getModel()
            ->newModelQuery()
            ->where('package_id', '=', 'metafox/video')
            ->where('name', '=', 'video.ffmpeg')
            ->delete();
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
