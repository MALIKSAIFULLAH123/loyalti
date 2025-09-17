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
        if (Schema::hasTable('chat_messages')) {
            return;
        }

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('room_id');
            DbTableHelper::totalColumns($table, ['attachment']);
            $table->string('type')->default('message');
            DbTableHelper::morphUserColumn($table, false);
            $table->mediumText('message')->nullable();
            $table->json('extra')->nullable()->default(null);
            $table->json('reactions')->nullable()->default(null);
            $table->json('seen_users')->nullable()->default(null);
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
        Schema::dropIfExists('chat_messages');
    }
};
