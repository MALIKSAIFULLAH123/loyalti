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
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'resolution' => 'mobile',
                ['menu', database_driver() == 'pgsql' ? 'ilike' : 'like', 'emoney.%']
            ])
            ->get()
            ->each(function ($item) {
                $item->update(['label' => str_replace('emoney::', 'ewallet::', $item->label)]);
            });

        \MetaFox\Menu\Models\MenuItem::query()
            ->where([
                'resolution' => 'mobile',
                'name'       => 'cancel'
            ])
            ->whereIn('menu', ['emoney.emoney_withdraw_request.detailActionMenu', 'emoney.emoney_withdraw_request.itemActionMenu'])
            ->update(['label' => 'ewallet::phrase.cancel_request']);
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
