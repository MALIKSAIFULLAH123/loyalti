<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use  MetaFox\BackgroundStatus\Models\BgsBackground;
use Illuminate\Support\Facades\DB;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models\
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('bgs_backgrounds')) {
            BgsBackground::query()->update([
                'image_path' => DB::raw('server_id'),
                'server_id'  => 'asset',
            ]);
        }

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
