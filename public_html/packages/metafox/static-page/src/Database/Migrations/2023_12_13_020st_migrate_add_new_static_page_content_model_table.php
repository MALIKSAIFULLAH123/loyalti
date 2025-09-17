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
        if (Schema::hasTable('static_page_contents')) {
            return;
        }

        Schema::create('static_page_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('static_page_id');
            $table->mediumText('text');
            $table->string('locale', 10);
            $table->timestamps();

            $table->unique(['static_page_id', 'locale'], 'static_page_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('static_page_contents');
    }
};
