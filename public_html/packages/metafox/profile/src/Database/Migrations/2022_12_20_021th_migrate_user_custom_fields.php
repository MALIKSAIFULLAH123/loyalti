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
        if (!Schema::hasTable('user_custom_fields')) {
            Schema::create('user_custom_fields', function (Blueprint $table) {
                $table->id('id');
                $table->unsignedInteger('section_id')->nullable();
                $table->string('field_name', 150);
                $table->unsignedInteger('ordering')->default(0);
                $table->string('type_id', 150)->nullable();
                $table->string('edit_type', 64)->nullable(); // TextArea, Etc.
                $table->string('view_type', 64)->nullable(); // Text, String, Etc.
                $table->string('var_type', 32)->default('string');
                $table->unsignedBigInteger('privacy')->default(0);
                $table->unsignedTinyInteger('is_active')->default(0);
                $table->unsignedTinyInteger('is_required')->default(0);
                $table->unsignedTinyInteger('is_feed')->default(0);
                $table->unsignedTinyInteger('is_register')->default(0);
                $table->unsignedTinyInteger('is_search')->default(0);
                $table->unsignedTinyInteger('has_label')->default(1);
                $table->unsignedTinyInteger('has_description')->default(1);
                $table->mediumText('extra')->nullable();
                $table->unique(['field_name']);
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
        Schema::dropIfExists('user_custom_fields');
    }
};
