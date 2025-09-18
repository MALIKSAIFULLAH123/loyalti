<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MetaFox\Group\Models\IntegratedModule;
use MetaFox\Menu\Models\MenuItem;

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
        if (!Schema::hasTable('group_integrated_modules')) {
            return;
        }

        $cursor = MenuItem::query()->where('menu', IntegratedModule::MENU_NAME)->where('resolution', 'web')->cursor();

        foreach ($cursor as $item) {
            if (!$item instanceof MenuItem) {
                continue;
            }

            IntegratedModule::withoutEvents(function () use ($item) {
                IntegratedModule::query()->where('name', $item->name)->whereNot('name', 'like', '%::%.%')->update(['label' => $item->label_var]);
            });
        }
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
