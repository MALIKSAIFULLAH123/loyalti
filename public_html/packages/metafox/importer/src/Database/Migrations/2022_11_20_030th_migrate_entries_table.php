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
        if (!Schema::hasColumn('importer_entries', 'filename')) {
            Schema::table('importer_entries', function (Blueprint $table) {
                $table->string('filename')->nullable();
                $table->integer('entry_index')->default(0);
            });
        }

        // to do here
    }
};
