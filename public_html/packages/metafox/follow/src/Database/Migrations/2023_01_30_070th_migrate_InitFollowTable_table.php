<?php

use Illuminate\Database\Migrations\Migration;

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
//        if (!Schema::hasTable('follows')) {
//            Schema::create('follows', function (Blueprint $table) {
//                $table->bigIncrements('id');
//                DbTableHelper::morphUserColumn($table);
//                DbTableHelper::morphOwnerColumn($table);
//
//                $table->index(['user_id', 'owner_id']);
//                $table->timestamps();
//            });
//        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
//        Schema::dropIfExists('follows');
    }
};
