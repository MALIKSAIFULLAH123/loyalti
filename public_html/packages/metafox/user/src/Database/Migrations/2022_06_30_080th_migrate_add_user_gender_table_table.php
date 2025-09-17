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
        if (!Schema::hasTable('user_gender')) {
            Schema::create('user_gender', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('phrase', 255)->nullable(false);
                $table->unsignedTinyInteger('is_custom')->default(1);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('user_profiles')) {
            if (Schema::hasColumn('user_profiles', 'gender')) {
                Schema::table('user_profiles', function (Blueprint $table) {
                    $table->unsignedBigInteger('gender_id')->default(0);
                    $table->dropColumn('gender');
                });
            }
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
        Schema::dropIfExists('user_gender');

        if (Schema::hasTable('user_profiles')) {
            if (!Schema::hasColumn('user_profiles', 'gender')) {
                Schema::table('user_profiles', function (Blueprint $table) {
                    $table->tinyInteger('gender')->default(0);
                });
            }

            if (Schema::hasColumn('user_profiles', 'gender_id')) {
                Schema::table('user_profiles', function (Blueprint $table) {
                    $table->dropColumn('gender_id');
                });
            }
        }
    }
};
