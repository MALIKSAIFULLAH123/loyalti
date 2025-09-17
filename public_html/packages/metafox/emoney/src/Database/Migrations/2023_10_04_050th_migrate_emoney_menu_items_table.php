<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\PackageManager;

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

        $this->handleMenus();
    }

    private function handleMenus(): void
    {
        $package = 'metafox/emoney';

        foreach (['web', 'admin'] as $type) {
            $filename = sprintf('resources/menu/%s.php', $type);

            $items = PackageManager::readFile($package, $filename);

            if (!$items) {
                continue;
            }

            foreach ($items as $key => $item) {
                if (!\Illuminate\Support\Arr::has($item, 'to')) {
                    \Illuminate\Support\Arr::forget($items, $key);
                }
            }

            if (!count($items)) {
                continue;
            }

            foreach ($items as $item) {
                $where = array_merge([
                    'parent_name' => '',
                    'resolution' => $type
                ], Arr::only($item, ['menu', 'resolution', 'parent_name', 'name']));

                $model = \MetaFox\Menu\Models\MenuItem::query()
                    ->where($where)
                    ->first();

                if (!$model instanceof \MetaFox\Menu\Models\MenuItem) {
                    continue;
                }

                $model->fill([
                    'to' => Arr::get($item, 'to')
                ]);

                $model->save();
            }
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
