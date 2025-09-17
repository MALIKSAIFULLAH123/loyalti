<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;

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
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        MenuItem::query()
            ->where('module_id', 'user')
            ->where('menu', 'user.user.profileActionMenu')
            ->where('name', 'confirm_request')
            ->where('resolution', 'web')
            ->update([
                'label' => 'user::phrase.accept_friend_request',
            ]);

        MenuItem::query()
            ->where('module_id', 'user')
            ->where('menu', 'user.user.profileActionMenu')
            ->where('name', 'delete_request')
            ->where('resolution', 'web')
            ->update([
                'label' => 'user::phrase.delete_friend_request',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
