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
        if (!Schema::hasTable('announcement_closes')) {
            Schema::create('announcement_closes', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);

                $table->unsignedBigInteger('announcement_id');

                $table->timestamps();

                $table->unique(['user_id', 'announcement_id']);
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
        Schema::dropIfExists('announcement_closes');
    }
};
