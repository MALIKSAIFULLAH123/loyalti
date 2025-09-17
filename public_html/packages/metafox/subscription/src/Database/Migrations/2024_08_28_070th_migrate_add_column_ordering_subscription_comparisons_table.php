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
        if (Schema::hasTable('subscription_comparisons')) {
            if (!Schema::hasColumn('subscription_comparisons', 'ordering')) {
                Schema::table('subscription_comparisons', function (Blueprint $table) {
                    $table->integer('ordering')->default(0);
                });
            }
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
        Schema::dropColumns('subscription_comparisons', 'ordering');
    }
};
