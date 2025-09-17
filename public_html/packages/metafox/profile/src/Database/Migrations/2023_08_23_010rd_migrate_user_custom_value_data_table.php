<?php

use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Profile\Jobs\MigrateUserCustomValueJob;
use MetaFox\Profile\Support\CustomField;

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
        if (Schema::hasTable('user_custom_option_data')) {
            return;
        }

        Schema::create(
            'user_custom_option_data',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('item_id')->index();
                $table->unsignedInteger('custom_option_id')->index();
            }
        );
        MigrateUserCustomValueJob::dispatchSync();

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('user_custom_option_data');
    }
};
