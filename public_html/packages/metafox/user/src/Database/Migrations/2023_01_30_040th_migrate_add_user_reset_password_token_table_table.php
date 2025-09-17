<?php

use MetaFox\Platform\Support\DbTableHelper;
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
        if (Schema::hasTable('user_reset_password_token')) {
            return;
        }

        Schema::create('user_reset_password_token', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('value', 128);
            $table->timestamp('expired_at');
            $table->timestamps();

            $table->index(['user_id', 'value'], 'user_token_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reset_password_token');
    }
};
