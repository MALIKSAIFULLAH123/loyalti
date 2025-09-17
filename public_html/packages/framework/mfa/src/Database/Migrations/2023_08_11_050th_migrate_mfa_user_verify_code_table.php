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
        if (Schema::hasTable('mfa_user_verify_code')) {
            return;
        }

        Schema::create('mfa_user_verify_code', function (Blueprint $table) {
            $table->increments('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('service', 50)->index();
            $table->string('action', 64);
            $table->string('code');
            $table->timestamp('expired_at');
            $table->timestamp('authenticated_at')->nullable();
            $table->unsignedInteger('is_active')->default(1);
            $table->timestamp('last_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa_user_verify_code');
    }
};
