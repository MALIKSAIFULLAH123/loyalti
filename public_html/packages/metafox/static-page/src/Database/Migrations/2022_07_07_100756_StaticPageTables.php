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
        if (!Schema::hasTable('static_pages')) {
            Schema::create('static_pages', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphUserColumn($table);
                DbTableHelper::morphOwnerColumn($table);

                DbTableHelper::moduleColumn($table);

                $table->boolean('is_active')
                    ->default(0);

                $table->boolean('is_phrase')
                    ->default(0);

                $table->boolean('parse_php')
                    ->default(0);

                $table->boolean('has_bookmark')
                    ->default(0);

                $table->boolean('full_size')
                    ->default(1);

                $table->mediumText('title');

                $table->mediumText('text');

                $table->string('slug', 200);

                $table->unsignedInteger('total_like')
                    ->default(0);

                $table->unsignedInteger('total_comment')
                    ->default(0);

                $table->unsignedInteger('total_share')
                    ->default(0);

                $table->unsignedInteger('total_view')
                    ->default(0);

                $table->unsignedInteger('total_tag')
                    ->default(0);

                $table->unsignedInteger('total_attachment')
                    ->default(0);

                $table->mediumText('disallow_access');
                $table->timestamps();
                $table->index('slug');
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
        Schema::dropIfExists('static_pages');
    }
};
