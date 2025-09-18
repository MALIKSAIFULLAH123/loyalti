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
        if (!Schema::hasTable('polls')) {
            return;
        }

        Schema::table('polls', function (Blueprint $table) {
            $table->text('pending_tagged_friends')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasColumn('polls', 'pending_tagged_friends')) {
            return;
        }

        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn(['pending_tagged_friends']);
        });
    }
};
