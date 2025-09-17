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
        \MetaFox\Menu\Models\MenuItem::query()->where([
            'menu' => 'core.accountMenu',
            'name' => 'story_archive',
        ])->update([
            'label' => 'story::phrase.story_archive',
        ]);
        // to do here
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
