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
        if (Schema::hasTable('core_admin_search')) {
            return;
        }

        Schema::create('core_admin_search', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid', 32)->unique();
            $table->string('group')->nullable();
            $table->string('lang', 12)->default('en');
            $table->string('caption')->nullable(true);
            DbTableHelper::moduleColumn($table);
            $table->mediumText('title');
            $table->mediumText('text');
            $table->mediumText('url');
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
        Schema::dropIfExists('core_admin_search');
    }
};
