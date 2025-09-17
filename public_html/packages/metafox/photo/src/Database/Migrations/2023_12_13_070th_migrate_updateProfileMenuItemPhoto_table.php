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
            ->where([
                'module_id'  => 'photo',
                'resolution' => 'mobile',
                'menu'       => 'group.group.profileMenu',
                'name'       => 'photo_set',
            ])->delete();

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'resolution' => 'mobile',
                'name'       => 'album',
            ])->whereIn('menu', [
                'photo.photo.headerItemActionOnPageProfileMenu',
                'photo.photo.headerItemActionOnGroupProfileMenu',
            ])->delete();

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'resolution' => 'mobile',
                'name'       => 'media',
            ])->whereIn('menu', [
                'photo.photo.headerItemActionOnPageProfileMenu',
                'photo.photo.headerItemActionOnGroupProfileMenu',
            ])->delete();

        // to do here
    }

    /**s
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
