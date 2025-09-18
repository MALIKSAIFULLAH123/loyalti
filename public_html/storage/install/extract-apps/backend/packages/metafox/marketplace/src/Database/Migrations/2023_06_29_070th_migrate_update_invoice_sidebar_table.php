<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use MetaFox\Platform\MetaFoxConstant;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;

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
        if (!Schema::hasTable('core_menu_items')) {
            return;
        }

        $resolutions = [MetaFoxConstant::RESOLUTION_MOBILE, MetaFoxConstant::RESOLUTION_WEB];

        MenuItem::query()
            ->whereIn('resolution', $resolutions)
            ->where('menu', 'marketplace.sidebarMenu')
            ->where('name', 'invoice')
            ->update([
                'name'  => 'bought_invoice',
                'label' => 'marketplace::phrase.bought_invoices',
                'to'    => '/marketplace/invoice-bought',
                'value' => 'viewBoughtInvoices',
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
