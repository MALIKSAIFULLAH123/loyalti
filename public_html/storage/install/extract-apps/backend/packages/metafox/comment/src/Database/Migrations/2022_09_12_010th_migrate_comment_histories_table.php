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
        $this->initCommentHistory();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_histories');
    }

    private function initCommentHistory()
    {
        if (!Schema::hasTable('comment_histories')) {
            Schema::create('comment_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('comment_id')->index();

                DbTableHelper::morphUserColumn($table);
                $table->mediumText('content');
                $table->unsignedBigInteger('item_id')->default(0);
                $table->string('item_type', 32)->nullable()->default('photo');
                $table->mediumText('params')->nullable();
                $table->string('phrase')->nullable();
                $table->mediumText('tagged_user_ids')->nullable();
                $table->timestamps();
            });
        }
    }
};
