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
        if (Schema::hasTable('user_stats_activities')) {
            return;
        }

        Schema::create('user_stats_activities', function (Blueprint $table) {
            $table->integerIncrements('id')->unsigned()->index();
            \MetaFox\Platform\Support\DbTableHelper::morphUserColumn($table);
            $table->dateTime('activity_at')->index();
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
        Schema::dropIfExists('user_stats_activities');
    }
};
