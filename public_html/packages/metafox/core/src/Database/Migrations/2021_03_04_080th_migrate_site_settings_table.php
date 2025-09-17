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
        if (!Schema::hasTable('core_site_settings')) {
            Schema::create('core_site_settings', function (Blueprint $table) {
                $table->id();
                DbTableHelper::moduleColumn($table);
                $table->string('name');
                $table->string('config_name')->nullable();
                $table->unsignedTinyInteger('is_auto')
                    ->default(1)
                    ->comment('autoload whenever settings is boot?')
                    ->index();
                $table->string('env_var')
                    ->nullable(true)
                    ->comment('environment variable');
                $table->unsignedTinyInteger('is_public')
                    ->default(1)
                    ->comment('should return to web, mobile');
                $table->string('type')
                    ->default('string')
                    ->comment('boolean, string, integer');
                $table->mediumText('value_actual')
                    ->nullable()
                    ->comment('json serialized');
                $table->mediumText('value_default')
                    ->nullable()
                    ->comment('json serialized');
                $table->timestamps();
                $table->unique(['name']);
                $table->unique(['config_name']);
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
        Schema::dropIfExists('core_site_settings');
    }
};
