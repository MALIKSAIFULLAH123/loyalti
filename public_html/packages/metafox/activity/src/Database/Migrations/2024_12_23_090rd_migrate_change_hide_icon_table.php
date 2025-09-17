<?php

use Illuminate\Database\Migrations\Migration;

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
        \MetaFox\Menu\Models\MenuItem::query()
            ->where('menu', 'feed.feed.itemActionMenu')
            ->where('resolution', 'web')
            ->whereIn('name', [
                'hide_all_user', 'hide_all_owner',
                'hide_all_shared_user', 'hide_all_shared_owner',
            ])
            ->update(['icon' => 'ico-eye-off-o']);
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
