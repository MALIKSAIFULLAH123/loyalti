<?php

use Illuminate\Database\Migrations\Migration;
use MetaFox\Like\Models\Reaction;

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
        if (!Reaction::query()->exists()) {
            return;
        }

        $firstReaction = Reaction::query()->where('is_default', 1)
            ->where('is_active', 1)->first();

        if (!$firstReaction instanceof Reaction) {
            $firstReaction = Reaction::query()->where('id', 1)->update([
                'is_active'  => 1,
                'is_default' => 1,
            ]);
        }

        Reaction::query()->whereNot('id', $firstReaction->entityId())
            ->update([
                'is_default' => 0,
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
