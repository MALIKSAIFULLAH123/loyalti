<?php

use Illuminate\Database\Migrations\Migration;
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
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'resolution' => 'mobile',
            'name'       => 'marketplace',
        ])->whereIn('menu', [
            'marketplace.marketplace.headerItemActionOnUserProfileMenu',
            'marketplace.marketplace.headerItemActionOnPageProfileMenu',
            'marketplace.marketplace.headerItemActionOnGroupProfileMenu',
        ])->delete();

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('updateProfileMenuItemListing');
    }
};
