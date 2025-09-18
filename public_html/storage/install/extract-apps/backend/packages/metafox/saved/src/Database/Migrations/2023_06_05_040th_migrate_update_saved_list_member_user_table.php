<?php

use MetaFox\Platform\Support\DbTableHelper;
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
        if (!Schema::hasColumn('saved_list_members', 'user_type')) {
            Schema::table('saved_list_members', function (Blueprint $table) {
                $table->string('user_type')
                    ->nullable(false)
                    ->default('user');
                $table->index('user_id');
                $table->index(['user_id', 'user_type'], 'ix_saved_list_members_user_morph');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('saved_list_members', 'user_type')) {
            Schema::table('saved_list_members', function (Blueprint $table) {
                $table->dropColumn(['user_type']);
            });
        }
    }
};
