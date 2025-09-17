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
        if (!Schema::hasTable('user_delete_feedback')) {
            return;
        }

        Schema::table('user_delete_feedback', function (Blueprint $table) {
            $table->string('phone_number', 50)->nullable();
            $table->mediumText('extra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('user_delete_feedback')) {
            return;
        }

        Schema::dropColumns('user_delete_feedback', ['phone_number', 'extra']);
    }
};
