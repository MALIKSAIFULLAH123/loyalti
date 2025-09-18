<?php

use Illuminate\Database\Migrations\Migration;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $legacySetting = \MetaFox\Core\Models\SiteSetting::query()
            ->where('module_id', 'video')
            ->where('name', 'video.video_service_to_process_video')
            ->first();

        if ($legacySetting?->value_actual == null) {
            return;
        }

        $newSetting = \MetaFox\Core\Models\SiteSetting::query()
            ->where('module_id', 'video')
            ->where('name', 'video.video_service')
            ->whereNull('value_actual')
            ->first();

        if (empty($newSetting)) {
            return;
        }

        $newSetting->update([
            'value_actual' => $legacySetting->value_actual
        ]);
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
