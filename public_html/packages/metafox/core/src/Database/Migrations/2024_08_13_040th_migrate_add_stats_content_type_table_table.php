<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

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
        if (Schema::hasTable('stats_content_types')) {
            return;
        }

        Schema::create('stats_content_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique('stats_content_name_idx');
            $table->string('icon')->nullable();
            $table->string('to')->nullable();
            $table->integer('ordering')->default(0);
            $table->unsignedTinyInteger('is_modified')
            ->default(0)
            ->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('stats_content_types');
    }
};
