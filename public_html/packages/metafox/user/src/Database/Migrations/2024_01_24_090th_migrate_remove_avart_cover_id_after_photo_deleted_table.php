<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\User\Models\UserProfile;

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
        if (!Schema::hasTable('user_profiles')) {
            return;
        }

        //Update avatar
        $query = UserProfile::query()
            ->where('avatar_id', '>', 0)
            ->whereDoesntHave('avatar');

        foreach ($query->cursor() as $user) {
            if (!$user instanceof UserProfile) {
                continue;
            }

            $user->updateQuietly([
                'avatar_id'      => 0,
                'avatar_file_id' => 0,
            ]);
        }

        // Update cover
        $query = UserProfile::query()
            ->where('cover_id', '>', 0)
            ->whereDoesntHave('cover');

        foreach ($query->cursor() as $user) {
            if (!$user instanceof UserProfile) {
                continue;
            }

            $user->updateQuietly([
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
