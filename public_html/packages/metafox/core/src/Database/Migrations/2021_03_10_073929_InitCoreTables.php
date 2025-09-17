<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Core\Models\Privacy;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * @ignore
 * @codeCoverageIgnore
 */

return new class () extends Migration {
    public const SOLUTION = 2;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('core_items')) {
            Schema::create('core_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_type');
                $table->unsignedBigInteger('original_item_id')->nullable();
            });
        }

        /****************************************************
         * PRIVACY LEVEL:
         * - level 1: owner
         * - level 2: friend, custom friend list, group member
         * - level 3: friends of friends
         *
         * PRIVACY DIVISION
         *
         * @example
         * - department -> company -> group
         * - class -> school
         *
         ****************************************************/

        /****************************************************
         * SOLUTION 1:
         * SUPPORT LEVEL: 1, 2, 3 using UNION
         * SUPPORT DIVISION: Union Other
         ****************************************************/
        if (!Schema::hasTable('core_privacy_customs')) {
            Schema::create('core_privacy_customs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('item_id');
                $table->string('item_type');
                $table->unsignedBigInteger('friend_list_id');
            });
        }

        /****************************************************
         * SOLUTION 02
         * SUPPORT LEVEL: 1, 2 Using JOIN:  blogs X privacy_stream ON(item_id, item_type=blog) X privacy_data ON(privacy_id, user_id=2)
         * SUPPORT DIVISION: Yes
         * Pros: Centralize all privacy stream (Good for global search)
         * Cons: How much data ?
         ****************************************************/
        if (!Schema::hasTable('core_privacy')) {
            Schema::create('core_privacy', function (Blueprint $table) {
                $table->bigIncrements('privacy_id');
                DbTableHelper::morphItemColumn($table);
                DbTableHelper::morphOwnerColumn($table);
                $table->unsignedInteger('privacy') //Todo: index
                    ->default(0);
                $table->string('privacy_type');
                DbTableHelper::morphUserColumn($table);
            });
            $privacyListModel = (new Privacy());
            $tablePrefix      = DB::getTablePrefix();

            $tableName  = $tablePrefix . $privacyListModel->getTable();
            $primaryKey = $privacyListModel->getKeyName();

            if (DB::getDefaultConnection() === 'pgsql') {
                DB::statement("ALTER SEQUENCE {$tableName}_{$primaryKey}_seq RESTART WITH 100;");
            } else {
                DB::statement("ALTER TABLE {$tableName} AUTO_INCREMENT = 100;");
            }
        }

        // relation privacy & user
        if (!Schema::hasTable('core_privacy_members')) {
            Schema::create('core_privacy_members', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                DbTableHelper::privacyIdColumn($table);
                $table->unique(['user_id', 'privacy_id']);
            });
        }

        if (!Schema::hasTable('core_privacy_streams')) {
            Schema::create('core_privacy_streams', function (Blueprint $table) {
                $table->bigIncrements('id');
                DbTableHelper::privacyIdColumn($table);
                DbTableHelper::morphItemColumn($table);
            });
        }

        /****************************************************
         * SOLUTION 03:
         * Required privacy_list, privacy_data but privacy stream.
         * SUPPORT LEVEL: 1, 2 Using JOIN:  blogs X blog_privacy_stream (item_id) X blog_privacy_data (privacy_id, user_id=2)
         * SUPPORT DIVISION: Yes
         * Pros:
         * - Partial data to multiple space.
         * Cons:
         * - Each content types must have  stream table
         ****************************************************/

        if (!Schema::hasTable('core_stats_contents')) {
            Schema::create('core_stats_contents', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('label');
                $table->unsignedInteger('value')->default(0);
                $table->string('period', 100)->nullable();
                $table->timestamp('created_at')->index();
            });
        }

        $this->createLinkTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('core_privacy_members');
        Schema::dropIfExists('core_network_list');
        Schema::dropIfExists('core_network_data');
        Schema::dropIfExists('core_privacy_customs');
        Schema::dropIfExists('core_privacy_list');
        Schema::dropIfExists('core_privacy_data');
        Schema::dropIfExists('core_privacy_stream');
        Schema::dropIfExists('privacy');
        Schema::dropIfExists('privacy_list');
        Schema::dropIfExists('privacy_stream');
        Schema::dropIfExists('privacy_data');
        Schema::dropIfExists('privacy_customs');
        Schema::dropIfExists('privacy');
        Schema::dropIfExists('core_items');
        Schema::dropIfExists('core_stats_contents');
        Schema::dropIfExists('core_privacy');
        Schema::dropIfExists('core_privacy_streams');
        Schema::dropIfExists('core_links');
        Schema::dropIfExists('core_attachments');
        Schema::dropIfExists('core_attachment_file_types');
    }

    protected function createLinkTable(): void
    {
        if (!Schema::hasTable('core_links')) {
            Schema::create('core_links', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::setupResourceColumns($table, true, true, true, false);

                DbTableHelper::totalColumns($table, ['like', 'comment', 'reply', 'share']);

                $table->string('title', 255);
                $table->text('link')->nullable();
                $table->text('host')->nullable();
                $table->text('image')->nullable();
                $table->text('description')->nullable();
                $table->text('feed_content')->nullable();
                $table->tinyInteger('has_embed')->default(0);
                DbTableHelper::locationColumn($table);
                DbTableHelper::approvedColumn($table);

                $table->timestamps();
            });
        }
    }
};
