<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
    public function up(): void
    {
        if (!Schema::hasTable('phrases')) {
            Schema::create('phrases', function (Blueprint $table) {
                $table->id();
                $table->string('key')->index();
                $table->string('name', 128);
                $table->string('group', 32)->index();
                $table->string('namespace', 32)->default('*')->index();
                $table->string('package_id')->default('core')->index();
                $table->string('locale')->default('en')->index();
                $table->string('is_modified')->default(0);
                $table->text('text');
                $table->unique(['locale', 'key'], 'core_phrase_uniq');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('phrases');
    }
};
