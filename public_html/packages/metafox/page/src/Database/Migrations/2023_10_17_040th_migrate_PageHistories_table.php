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
        if (Schema::hasTable('page_histories')) {
            return;
        }

        Schema::create('page_histories', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('page_id')->index();

            \MetaFox\Platform\Support\DbTableHelper::morphUserColumn($table);

            $table->string('type')->index();
            $table->json('extra')->nullable()->default(null);
            $table->timestamps();
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
        Schema::dropIfExists('page_histories');
    }
};
