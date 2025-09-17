<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;

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
        $this->cleanUpDuplicate();

        if (!Schema::hasColumn('apt_settings', 'role_id')) {
            return;
        }

        if (!Schema::hasColumn('apt_settings', 'name')) {
            return;
        }

        Schema::table('apt_settings', function (Blueprint $table) {
            $table->unique(['name', 'role_id'], 'unique_name_role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    public function cleanUpDuplicate(): void
    {
        if (!Schema::hasTable('apt_settings')) {
            return;
        }

        $allRoles = resolve(RoleRepositoryInterface::class)->all()->pluck('id')->toArray();
        if (empty($allRoles)) {
            return;
        }

        DbTableHelper::deleteDuplicatedRows('apt_settings', 'id', ['role_id', 'name']);
    }
};
