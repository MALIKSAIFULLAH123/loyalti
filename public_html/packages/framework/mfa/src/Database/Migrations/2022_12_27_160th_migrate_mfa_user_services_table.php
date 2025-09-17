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
        if (Schema::hasTable('mfa_user_services')) {
            return;
        }

        // legacy table
        Schema::dropIfExists('user_multi_factor_token');

        Schema::create('mfa_user_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphUserColumn($table);
            $table->string('service', 50)->index();
            $table->text('value');
            $table->text('extra');
            $table->unsignedTinyInteger('is_active')->default(0);
            $table->timestamp('last_authenticated')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'service'], 'ix_user_service_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa_user_services');
    }
};
