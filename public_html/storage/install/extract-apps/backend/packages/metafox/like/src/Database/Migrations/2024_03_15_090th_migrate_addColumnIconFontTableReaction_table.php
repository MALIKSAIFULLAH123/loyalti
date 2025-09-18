<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        Schema::table('like_reactions', function (Blueprint $table) {
            if (!Schema::hasColumn('like_reactions', 'icon_font')) {
                $table->string('icon_font')->default('ico-thumbup-o');
            }
        });

        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'like.admin',
            'name' => 'create-reaction',
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
        Schema::table('like_reactions', function (Blueprint $table) {
            if (Schema::hasColumn('like_reactions', 'icon_font')) {
                $table->dropColumn('icon_font');
            }
        });
    }
};
