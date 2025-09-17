<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasColumn('user_custom_fields', 'key')) {
            return;
        }

        Schema::table('user_custom_fields', function (Blueprint $table) {
            $table->string('key')->nullable()->after('section_id')->index();
        });

        \MetaFox\Profile\Models\Field::query()->update([
            'key' => DB::raw("CONCAT('field_user_', field_name)"),
        ]);

        Schema::table('user_custom_fields', function (Blueprint $table) {
            $table->string('key')->unique()->change();
        });

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('user_custom_fields', 'key')) {
            Schema::table('user_custom_fields', function (Blueprint $table) {
                $table->dropColumn('key');
            });
        }
    }
};
