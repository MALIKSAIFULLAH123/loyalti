<?php

use MetaFox\Platform\Support\DbTableHelper;
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
        if (!Schema::hasTable('sevents')) {
            return;
        }
        if (!Schema::hasColumn('sevents', 'course_id')) {
            Schema::table('sevents', function (Blueprint $table) {
                $table->unsignedInteger('course_id')
                    ->default(0);
            });
        }
    }
};
