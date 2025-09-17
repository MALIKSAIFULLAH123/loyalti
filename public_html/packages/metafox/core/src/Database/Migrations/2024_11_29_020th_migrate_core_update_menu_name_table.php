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
        $updates = [
            [
                'where'  => 'core.leftFooterMenu',
                'update' => 'core.primaryFooterMenu',
                'title'  => 'core::phrase.primary_footer_menu',
            ],
            [
                'where'  => 'core.rightFooterMenu',
                'update' => 'core.secondaryFooterMenu',
                'title'  => 'core::phrase.secondary_footer_menu',
            ],
        ];

        foreach ($updates as $update) {
            \MetaFox\Menu\Models\Menu::query()->where('name', $update['where'])->update([
                'name'  => $update['update'],
                'title' => $update['title'],
            ]);
            \MetaFox\Menu\Models\MenuItem::query()->where('menu', $update['where'])->update(['menu' => $update['update']]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {}
};
