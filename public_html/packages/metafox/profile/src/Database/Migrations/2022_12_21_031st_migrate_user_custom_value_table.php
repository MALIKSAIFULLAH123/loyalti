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
        if (!Schema::hasTable('user_custom_value')) {
            Schema::create('user_custom_value', function (Blueprint $table) {
                $table->unsignedBigInteger('id', true);
                $table->unsignedBigInteger('user_id');
                $table->string('user_type');
                $table->unsignedInteger('field_id');
                $table->mediumText('field_value_text')->nullable();
                $table->unsignedSmallInteger('ordering')->nullable();
                $table->unsignedBigInteger('privacy')->default(0);

                $table->unique(['user_id', 'field_id', 'ordering']);
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
        Schema::dropIfExists('user_custom_value');
    }
};
