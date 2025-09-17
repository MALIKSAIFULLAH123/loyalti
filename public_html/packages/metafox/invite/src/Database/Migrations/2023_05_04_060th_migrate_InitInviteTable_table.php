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
        if (Schema::hasTable('invites')) {
            return;
        }

        Schema::create('invites', function (Blueprint $table) {
            $table->bigIncrements('id');
            DbTableHelper::morphUserColumn($table);
            DbTableHelper::morphOwnerColumn($table, true);

            $table->unsignedTinyInteger('status_id')->default(0);
            $table->string('email')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('message')->nullable();
            $table->string('code')->index();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('invites');
    }
};
