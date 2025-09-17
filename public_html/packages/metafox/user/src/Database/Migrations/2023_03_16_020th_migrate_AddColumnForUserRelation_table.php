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
        if (Schema::hasColumns('user_relation', ['image_file_id', 'is_active', 'is_custom'])) {
            return;
        }

        Schema::table('user_relation', function (Blueprint $table) {
            DbTableHelper::imageColumns($table);
            $table->unsignedTinyInteger('is_active')->default(0);
            $table->unsignedTinyInteger('is_custom')->default(0);
            $table->string('relation_name')->nullable()->unique();
        });

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
