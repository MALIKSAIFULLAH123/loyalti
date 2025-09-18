<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Platform\Support\DbTableHelper;

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
        if (!Schema::hasTable('like_aggregations')) {
            Schema::create('like_aggregations', function (Blueprint $table) {
                $table->bigIncrements('id');

                DbTableHelper::morphItemColumn($table);

                $table->unsignedInteger('reaction_id')
                    ->default(0);

                $table->unsignedInteger('total_reaction')
                    ->default(1);

                $table->timestamps();

                $table->unique(['item_id', 'item_type', 'reaction_id']);
                $table->index(['item_id', 'item_type', 'reaction_id', 'total_reaction'], 'item_reaction_count');
            });
        }

        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('like_aggregations');
    }
};
