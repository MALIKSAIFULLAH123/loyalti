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
        if (!Schema::hasTable('phrases')) {
            return;
        }

        if (Schema::hasColumn('phrases', 'is_modified')) {
            return;
        }

        Schema::table('phrases', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_modified')
                ->default(0);
        });

        \MetaFox\Platform\Support\DbTableHelper::aggreateManualModified(\MetaFox\Localize\Models\Phrase::class);
    }
};
