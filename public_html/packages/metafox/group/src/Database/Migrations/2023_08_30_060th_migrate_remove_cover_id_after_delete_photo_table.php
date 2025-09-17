<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Group\Models\Group;

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
        if (!Schema::hasTable('groups')) {
            return;
        }

        // Update cover
        $query = Group::query()
            ->where('cover_id', '>', 0)
            ->whereDoesntHave('cover');

        foreach ($query->cursor() as $group) {
            if (!$group instanceof Group) {
                continue;
            }

            $group->updateQuietly([
                'cover_id'             => 0,
                'cover_file_id'        => 0,
                'cover_photo_position' => 0,
            ]);
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
