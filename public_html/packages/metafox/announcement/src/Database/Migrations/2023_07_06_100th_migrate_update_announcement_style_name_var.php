<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Announcement\Models\Style;

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
        if (!Schema::hasTable('announcement_styles')) {
            return;
        }

        $announcementStyles = [
            'success' => 'announcement::phrase.success',
            'info'    => 'announcement::phrase.info',
            'warning' => 'announcement::phrase.warning',
            'danger'  => 'announcement::phrase.danger',
        ];

        foreach ($announcementStyles as $name => $nameVar) {
            Style::query()->where('name', $name)->update(['name_var' => $nameVar]);
        }
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
