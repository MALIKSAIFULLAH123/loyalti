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
        $deleteAction = [
            'edit',
            'delete',
            'sponsor_in_feed',
            'remove_sponsor_in_feed',
        ];

        $updateOrdering = [
            'save'       => 1,
            'un-save'    => 1,
            'report'     => 2,
            'remove_tag' => 3,
        ];

        $this->handleMenuItem('feed.feed.itemActionMenuForProfile', $deleteAction, $updateOrdering);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }

    protected function handleMenuItem(string $menuName, array $deleteAction, array $updateOrdering)
    {
        $menuItem = new \MetaFox\Menu\Models\MenuItem();

        $menuItem->newModelQuery()->where('menu', $menuName)
            ->whereIn('name', $deleteAction)->delete();

        foreach ($updateOrdering as $key => $value) {
            $menuItem->newModelQuery()
                ->where('menu', $menuName)
                ->where('name', $key)
                ->update([
                    'ordering' => $value,
                ]);
        }
    }
};
