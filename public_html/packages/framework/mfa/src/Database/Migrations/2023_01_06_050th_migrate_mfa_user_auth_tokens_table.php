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
        if (Schema::hasTable('mfa_user_auth_tokens')) {
            return;
        }

        Schema::create('mfa_user_auth_tokens', function (Blueprint $table) {
            $table->increments('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('value', 128)->unique();
            $table->unsignedTinyInteger('is_authenticated')->index()->default(0);
            $table->timestamp('expired_at');
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
        Schema::dropIfExists('mfa_user_auth_tokens');
    }
};
