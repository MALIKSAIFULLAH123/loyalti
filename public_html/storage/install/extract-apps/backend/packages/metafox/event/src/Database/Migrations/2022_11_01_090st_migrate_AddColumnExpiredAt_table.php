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
        if (Schema::hasTable('event_invite_codes')) {
            Schema::table('event_invite_codes', function (Blueprint $table) {
                $table->timestamp('expired_at')->nullable();
            });
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
        if (Schema::hasTable('event_invite_codes')) {
            Schema::table('event_invite_codes', function (Blueprint $table) {
                $table->dropColumn(['expired_at']);
            });
        }
    }
};
