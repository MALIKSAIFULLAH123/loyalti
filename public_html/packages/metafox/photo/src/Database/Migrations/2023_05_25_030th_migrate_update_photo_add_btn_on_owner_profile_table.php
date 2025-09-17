<?php

use Illuminate\Database\Migrations\Migration;
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

        $needUpdateMenus = [
            'photo.photo.headerItemActionOnUserProfileMenu',
            'photo.photo.headerItemActionOnPageProfileMenu',
            'photo.photo.headerItemActionOnGroupProfileMenu',
        ];

        MenuItem::query()
            ->where('package_id', 'metafox/photo')
            ->where('resolution', 'web')
            ->where('name', 'media')
            ->whereIn('menu', $needUpdateMenus)
            ->update([
                'to'    => '/photo/add?owner_id=:id',
                'label' => 'photo::phrase.upload_photos',
            ]);

        MenuItem::query()
        ->where('package_id', 'metafox/photo')
        ->where('resolution', 'web')
        ->where('name', 'album')
        ->whereIn('menu', $needUpdateMenus)
        ->update([
            'to'    => '/photo/album/add?owner_id=:id',
            'label' => 'photo::phrase.create_new_album',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('update_photo_add_btn_on_owner_profile');
    }
};
