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
        if (!Schema::hasTable('importer_entries')) {
            Schema::create('importer_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('bundle_id');
                $table->unsignedInteger('priority');

                // morpth to metafox database resource.
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->string('resource_type')->nullable();

                // data ref to source object.
                $table->string('source', '16');
                $table->string('ref_id', '64');

                $table->unsignedInteger('total_retry')->default(0);
                $table->string('filename')->nullable();
                $table->integer('entry_index')->default(0);
                $table->string('status')->default('pending')
                    ->comment('pending, processing, failed');

                // track timestamp.
                $table->timestamps();

                // unique index
                $table->unique(['ref_id', 'source']);
                $table->index(['resource_id', 'resource_type']);
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
        Schema::dropIfExists('importer_entries');
    }
};
