<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedListItemView;

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
        if (!Schema::hasTable('save_list_item_views')) {
            Schema::create('save_list_item_views', function (Blueprint $table) {
                $table->bigInteger('list_id')->index()->nullable();
                $table->bigInteger('user_id')->index();
                $table->bigInteger('saved_id')->index();
                $table->index(['saved_id', 'user_id'], 'save_list_saved_users');
            });
        }

        //sync from save items is opened = 1 to table
        $savedOpened = Saved::query()->where('is_opened', 1)->get();
        foreach ($savedOpened as $saved) {
            /** @var Saved $saved */
            $arrListIds = $saved->savedLists()->pluck('list_id')->toArray();

            $data = [
                'list_id'  => null,
                'saved_id' => $saved->entityId(),
                'user_id'  => $saved->userId(),
            ];

            if (count($arrListIds) > 0) {
                foreach ($arrListIds as $key => $value) {
                    $data['list_id'] = $value;
                    SavedListItemView::query()->firstOrCreate($data);
                }
            }

            if (count($arrListIds) == 0) {
                SavedListItemView::query()->firstOrCreate($data);
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
        Schema::dropIfExists('save_list_item_views');
        //remove column is_opened on table saved_items
        Schema::dropColumns('saved_items', 'is_opened');
    }
};
