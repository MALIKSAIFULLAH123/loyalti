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
        if (!Schema::hasTable('user_verify')) {
            Schema::create('user_verify', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('action', 64);
                $table->timestamp('expires_at');
                DbTableHelper::morphUserColumn($table, true);
                $table->string('hash_code', 255);
                $table->string('email', 255);
                $table->unique('hash_code');
                $table->timestamp('created_at')->useCurrent();
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
        Schema::dropIfExists('user_verify');
    }
};
