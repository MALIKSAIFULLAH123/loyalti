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
        if (!Schema::hasTable('user_custom_field_visible_role_data')) {
            Schema::create('user_custom_field_visible_role_data', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('field_id')->index();
                $table->unsignedInteger('role_id')->index();
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
        Schema::dropIfExists('user_custom_field_visible_role_data');
    }
};
