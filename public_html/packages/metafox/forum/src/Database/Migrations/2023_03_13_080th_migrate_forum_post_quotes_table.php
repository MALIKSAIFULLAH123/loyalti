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
        if (!Schema::hasTable('forum_post_quotes')) {
            return;
        }

        Schema::table('forum_post_quotes', function (Blueprint $table) {
            $table->mediumText('quote_content')
                ->nullable();

            $table->bigInteger('quote_id', false, true)
                ->default(0)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('forum_post_quotes')) {
            return;
        }

        Schema::table('forum_post_quotes', function (Blueprint $table) {
            $table->dropColumn('quote_content');
        });
    }
};
