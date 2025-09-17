<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        if (!Schema::hasTable('user_entities') || Schema::hasColumn('user_entities', 'is_suggestion_searchable')) {
            return;
        }

        Schema::table('user_entities', function (Blueprint $table) {
            $table->boolean('is_suggestion_searchable')
                ->default(true)
                ->index('ue_is_suggestion_searchable');
        });

        if (!config('app.mfox_installed')) {
            return;
        }

        $data = app('events')->dispatch('user.user_entity.mass_migrate_suggestion_searchable.get_entity_types', []);

        if (!is_array($data) || !count($data)) {
            return;
        }

        foreach ($data as $app) {
            if (!is_array($app) || !count($app)) {
                continue;
            }

            foreach ($app as $entityType => $value) {
                if (!is_string($entityType) || !is_bool($value) || $value !== false) {
                    continue;
                }

                \Illuminate\Support\Facades\DB::table('user_entities')
                    ->where('entity_type', $entityType)
                    ->update(['is_suggestion_searchable' => false]);
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
        if (!Schema::hasTable('user_entities') || !Schema::hasColumn('user_entities', 'is_suggestion_searchable')) {
            return;
        }

        Schema::dropColumns('user_entities', 'is_suggestion_searchable');
    }
};
