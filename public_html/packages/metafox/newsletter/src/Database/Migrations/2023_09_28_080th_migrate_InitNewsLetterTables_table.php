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
        if (!Schema::hasTable('newsletters')) {
            Schema::create('newsletters', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('subject', 255);
                DbTableHelper::morphColumn($table, 'user');
                $table->integer('age_from')->nullable();
                $table->integer('age_to')->nullable();
                $table->integer('round')->default(5);
                $table->integer('status')->default(0);
                $table->text('user_roles')->nullable();
                $table->string('country_iso')->nullable();
                $table->unsignedBigInteger('gender_id')->default(0)->nullable();
                $table->tinyInteger('archive')->default(0);
                $table->tinyInteger('override_privacy')->default(0);
                $table->integer('total_sent')->default(0);
                $table->integer('total_users')->default(0);
                $table->bigInteger('last_sent_id')->default(0);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('newsletter_text')) {
            Schema::create('newsletter_text', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->mediumText('text_html');
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
        Schema::dropIfExists('newsletters');
        Schema::dropIfExists('newsletter_text');
    }
};
