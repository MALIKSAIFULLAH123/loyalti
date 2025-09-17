<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;

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
        if (Schema::hasTable('music_songs')) {
            Schema::table('music_songs', function (Blueprint $table) {
                $table->mediumText('description')->nullable()->change();
            });
        }

        MenuItem::query()->where([
            'menu' => 'music.sidebarMenu',
            'name' => 'add_song',
        ])->update(['to' => '/music/add']);

        MenuItem::query()->where([
            'menu' => 'music.sidebarMenu',
            'name' => 'add_album',
        ])->delete();

        MenuItem::query()->where([
            'menu' => 'music.sidebarMenu',
            'name' => 'add_playlist',
        ])->delete();

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('UpdateTableMusic');
    }
};
