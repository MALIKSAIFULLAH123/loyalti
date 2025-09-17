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
        if (!Schema::hasTable('storage_files')) {
            Schema::create('storage_files', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('origin_id')->nullable()->index();
                $table->unsignedTinyInteger('is_origin')->default(0);
                $table->string('storage_id', 32)->nullable();
                $table->string('target', 32)->nullable()->index();
                DbTableHelper::morphUserColumn($table, true);
                DbTableHelper::morphItemColumn($table, true);
                $table->mediumText('path')->nullable();
                $table->string('variant')->nullable();
                $table->string('original_name')->nullable();
                $table->string('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('extension')->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->string('package_id', 64)->nullable();
                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('storage_files');
    }
};
