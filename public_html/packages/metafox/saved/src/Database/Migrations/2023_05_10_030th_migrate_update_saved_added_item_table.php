<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Saved\Models\SavedList;

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
        if (!Schema::hasColumn('saved_lists', 'item_added_at')) {
            Schema::table('saved_lists', function (Blueprint $table) {
                $table->timestamp('item_added_at')->nullable();
            });
        }

        $savedLists = SavedList::query()->getModel()->get();

        foreach ($savedLists as $savedList) {
            $savedList->update(['item_added_at' => $savedList->updated_at]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('saved_lists', 'item_added_at')) {
            Schema::table('saved_lists', function (Blueprint $table) {
                $table->dropColumn(['item_added_at']);
            });
        }
    }
};
