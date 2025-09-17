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
        if (!Schema::hasTable('importer_bundle')) {
            Schema::create('importer_bundle', function (Blueprint $table) {
                $table->id();

                // example: phpfoxv4.
                $table->string('source', '16');
                $table->string('resource', '64');

                $table->unsignedInteger('priority')->default(0);

                // path to importing file.
                $table->string('filename');

                // total entry to import
                $table->unsignedInteger('entry_index')->default(0);
                $table->unsignedInteger('total_entry')->default(0);
                $table->unsignedInteger('total_retry')->default(0);
                $table->string('status')->default('pending');

                $table->unique(['filename']);
                // created atfile
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
        Schema::dropIfExists('importer_bundle');
    }
};
