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
        if (!Schema::hasTable('activity_histories')) {
            Schema::create('activity_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('feed_id')->index();
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::feedContentColumn($table);
                $table->mediumText('phrase')->nullable();
                $table->mediumText('extra')->nullable();
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
        Schema::dropIfExists('activity_histories');
    }
};
