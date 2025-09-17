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
        if (Schema::hasTable('chat_subscriptions')) {
            return;
        }

        Schema::create('chat_subscriptions', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('room_id');
            $table->string('name');
            $table->unsignedTinyInteger('is_favourite')->default(0);
            $table->unsignedTinyInteger('is_showed')->default(0);
            $table->unsignedTinyInteger('is_deleted')->default(0);
            DbTableHelper::morphUserColumn($table, false);
            $table->bigInteger('total_unseen')->default(0);
            $table->timestamp('rejoin_at')->nullable()->default(null);
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
        Schema::dropIfExists('chat_subscriptions');
    }
};
