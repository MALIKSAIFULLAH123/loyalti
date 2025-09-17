<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @ignore
 * @codeCoverageIgnore
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);

                $table->unsignedTinyInteger('is_active')->default(1);
                $table->unsignedTinyInteger('can_be_closed')->default(1);
                $table->unsignedTinyInteger('show_in_dashboard')->default(0);
                $table->timestamp('start_date')->nullable();
                $table->string('country_iso', 10)->nullable();
                $table->string('user_group', 255)->nullable();
                $table->unsignedInteger('gender')->nullable();
                $table->unsignedInteger('age_from')->nullable();
                $table->unsignedInteger('age_to')->nullable();
                $table->string('gmt_offset', 50)->nullable();

                $table->unsignedBigInteger('style_id');

                $table->text('subject_var');
                $table->text('intro_var');
                DbTableHelper::totalColumns($table, ['view']);
                $table->timestamps();

                $table->index(
                    ['can_be_closed', 'start_date'],
                    'announcement_indexes'
                );
            });
        }

        // Create Announcement Text table
        DbTableHelper::textTable('announcement_text');

        if (!Schema::hasTable('announcement_hidden')) {
            Schema::create('announcement_hidden', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);

                $table->unsignedBigInteger('announcement_id');

                $table->timestamps();

                $table->unique(['user_id', 'announcement_id']);
            });
        }

        if (!Schema::hasTable('announcement_styles')) {
            Schema::create('announcement_styles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('icon_image')->nullable();
                $table->string('icon_font')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('announcement_styles');
        Schema::dropIfExists('announcement_hidden');
        Schema::dropIfExists('announcement_text');
        Schema::dropIfExists('announcements');
    }
};
