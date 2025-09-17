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
        $this->createPermissionTable();
        $this->createPermissionConfigTable();
        $this->createModeratorHasAccessTable();
    }

    protected function createPermissionTable(): void
    {
        if (Schema::hasTable('forum_permissions')) {
            return;
        }

        Schema::create('forum_permissions', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);

            $table->string('var_name', 30)
                ->unique();
        });
    }

    protected function createPermissionConfigTable(): void
    {
        if (Schema::hasTable('forum_permission_configs')) {
            return;
        }

        Schema::create('forum_permission_configs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('forum_id');

            $table->string('permission_name', 30);

            $table->unique(['forum_id', 'permission_name'], 'fpc_forum_perm');
        });
    }

    protected function createModeratorHasAccessTable(): void
    {
        if (Schema::hasTable('forum_moderator_has_access')) {
            return;
        }

        Schema::create('forum_moderator_has_access', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('forum_id');

            DbTableHelper::morphUserColumn($table);

            $table->string('permission_name', 30);

            $table->unique(['forum_id', 'permission_name', 'user_id'], 'fmha_moderator_access');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_permissions');
        Schema::dropIfExists('forum_permission_configs');
        Schema::dropIfExists('forum_moderator_has_access');
    }
};
