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
        Schema::create('packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('title');
            $table->string('icon');
            $table->string('path');
            $table->string('type')->nullable();  // app, theme, language
            $table->string('category')->nullable();
            $table->string('alias');
            $table->string('namespace');
            $table->string('name_studly');
            $table->string('version')->default('1.0.0');
            $table->string('latest_version')
                ->default('1.0.0');
            $table->text('description')
                ->nullable();
            $table->text('keywords')
                ->nullable();

            $table->unsignedInteger('store_id')->default(0);
            $table->string('store_url')->nullable();

            $table->string('author')->default('');
            $table->string('author_url')->default('');
            $table->string('internal_url')->default('');

            $table->string('frontend')->nullable();

            $table->string('mobile')->nullable();

            $table->string('internal_admin_url')->default('');

            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_installed')->default(0);
            $table->tinyInteger('is_bundled')->default(0);
            $table->tinyInteger('is_purchased')->default(0);
            $table->integer('order')->default(0);
            $table->string('bundle_status')->nullable(true);
            $table->text('providers')->nullable();
            $table->text('aliases')->nullable();
            $table->text('files')->nullable();
            $table->text('requires')->nullable();
            $table->integer('priority')->default(100);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->integer('is_core')->default(0);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
