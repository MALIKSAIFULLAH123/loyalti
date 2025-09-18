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
        if (!Schema::hasTable('videos') || Schema::hasColumns('videos', ['is_valid', 'verified_at'])) {
            return;
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->boolean('is_valid')->default(true)->after('in_process');
            $table->timestamp('verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (!Schema::hasTable('videos') || !Schema::hasColumn('videos', 'is_valid')) {
            return;
        }

        Schema::dropColumns('videos', 'is_valid');
    }
};
