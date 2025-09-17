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
        if (Schema::hasTable('chat_rooms')) {
            return;
        }

        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            // used for checking direct message from  by md5(sort[user1.id, user2.id])
            $table->string('uid')->nullable();
            $table->string('name')->nullable();
            DbTableHelper::morphUserColumn($table, false);
            DbTableHelper::morphOwnerColumn($table, false);
            // p = private group
            // d = direct message (chat 1-n)
            // c = public chanel
            // u =  only user (not room, subscription created :D)
            $table->unsignedTinyInteger('is_archived');
            $table->unsignedTinyInteger('is_readonly');
            $table->string('type', 1)->default('d');
            $table->timestamps();

            $table->index('uid');
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
        Schema::dropIfExists('chat_rooms');
    }
};
