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
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'group.group.profilePopoverMenu',
            'name' => 'edit',
        ])->update([
            'ordering' => 7,
            'label'    => 'group::phrase.manage',
            'icon'     => 'ico-pencilline-o',
        ]);
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'group.group.profilePopoverMenu',
            'name' => 'invite',
        ])->update([
            'ordering' => 10,
        ]);
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'group.group.profilePopoverMenu',
            'name' => 'report',
        ])->update([
            'ordering' => 14,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('updateOrderingItemMenuForGroup');
    }
};
