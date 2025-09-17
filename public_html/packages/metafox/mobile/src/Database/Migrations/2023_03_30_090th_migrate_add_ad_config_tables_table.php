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
        $this->addAdMobConfigTable();
        $this->addAdMobPageTable();
        $this->addAdMobConfigRoleDataTable();
        $this->addAdMobConfigPageDataTable();
    }

    protected function addAdMobConfigTable(): void
    {
        if (Schema::hasTable('ad_mob_configs')) {
            return;
        }

        Schema::create('ad_mob_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('name');
            $table->string('type', 100);
            $table->string('frequency_capping', 100)->nullable();
            $table->unsignedInteger('view_capping', false)->nullable();
            $table->unsignedInteger('time_capping_impression', false)->nullable();
            $table->string('time_capping_frequency', 50)->nullable();
            $table->mediumText('location_priority')->nullable();
            $table->unsignedTinyInteger('is_sticky')->default(0);
            DbTableHelper::activeColumn($table);
            $table->timestamps();
        });
    }

    protected function addAdMobPageTable(): void
    {
        if (Schema::hasTable('ad_mob_pages')) {
            return;
        }

        Schema::create('ad_mob_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('path')->index();
            DbTableHelper::moduleColumn($table);
            $table->unique(['path', 'package_id'], 'path_package_id');
            $table->timestamps();
        });
    }

    protected function addAdMobConfigRoleDataTable(): void
    {
        if (Schema::hasTable('ad_mob_config_role_data')) {
            return;
        }

        Schema::create('ad_mob_config_role_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('config_id')->index();
            $table->unsignedInteger('role_id')->index();
        });
    }

    protected function addAdMobConfigPageDataTable(): void
    {
        if (Schema::hasTable('ad_mob_config_page_data')) {
            return;
        }

        Schema::create('ad_mob_config_page_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('config_id')->index();
            $table->unsignedInteger('page_id')->index();
            $table->string('config_type');
            $table->index(['config_id', 'config_type', 'page_id'], 'config_page_type_query');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_mob_configs');
        Schema::dropIfExists('ad_mob_pages');
        Schema::dropIfExists('ad_mob_config_role_data');
        Schema::dropIfExists('ad_mob_config_page_data');
    }
};
