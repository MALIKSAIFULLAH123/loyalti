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
        if (!Schema::hasTable('group_changed_privacy')) {
            Schema::create('group_changed_privacy', function (Blueprint $table) {
                $table->integerIncrements('id');
                $table->unsignedInteger('group_id')->index();
                DbTableHelper::morphUserColumn($table);
                DbTableHelper::privacyColumn($table);
                DbTableHelper::privacyTypeColumn($table);
                $table->timestamp('expired_at');
                $table->unsignedTinyInteger('is_active')->default(1);
                $table->timestamps();
            });
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('GroupCronTabChangePrivacy');
    }
};
