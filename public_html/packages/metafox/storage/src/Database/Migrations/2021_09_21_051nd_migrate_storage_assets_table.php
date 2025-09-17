<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

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
        if (!Schema::hasTable('storage_assets')) {
            Schema::create('storage_assets', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::moduleColumn($table);
                $table->string('name')->nullable();
                $table->unsignedBigInteger('file_id')->nullable();
                $table->string('local_path');
                $table->timestamps();
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
        Schema::dropIfExists('storage_assets');
    }
};
