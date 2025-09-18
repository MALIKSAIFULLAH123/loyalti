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
            ->whereIn('name', ['createField', 'createSection'])
            ->where('menu', 'group.admin')
            ->delete();
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
