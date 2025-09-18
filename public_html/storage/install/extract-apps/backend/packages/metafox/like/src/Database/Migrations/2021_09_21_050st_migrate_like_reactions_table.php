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
        if (!Schema::hasTable('like_reactions')) {
            Schema::create('like_reactions', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('title');

                $table->unsignedTinyInteger('is_active')
                    ->default(1);

                $table->unsignedTinyInteger('is_default')
                    ->default(0);
                $table->string('color', 50)
                    ->default('#2681D5');
                DbTableHelper::imageColumns($table, 'icon_file_id');
                $table->string('image_path')->nullable();
                $table->string('icon_path')->nullable();
                $table->string('server_id')->nullable();

                $table->unsignedInteger('ordering')
                    ->default(0);

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
        Schema::dropIfExists('like_reactions');
    }
};
