<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Profile\Models\Profile;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Models\Structure;
use MetaFox\Profile\Support\CustomField;

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
        if (!Schema::hasColumns('user_custom_profiles', ['title', 'description',])) {
            Schema::table('user_custom_profiles', function (Blueprint $table) {
                $table->string('title')->nullable();
                $table->string('description', 500)->nullable();
            });
        }

        if (!Schema::hasColumns('user_custom_structure', ['section_id'])) {
            Schema::table('user_custom_structure', function (Blueprint $table) {
                $table->unsignedInteger('section_id');
            });
        }

        if (Schema::hasColumns('user_custom_structure', ['field_id', 'ordering'])) {
            Schema::table('user_custom_structure', function (Blueprint $table) {
                $table->dropColumn('field_id');
                $table->dropColumn('ordering');
            });
        }

        $this->handleInsertStructure();
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumns('user_custom_structure', ['section_id',])) {
            Schema::table('user_custom_structure', function (Blueprint $table) {
                $table->dropColumn(['section_id',]);
            });
        }

        if (Schema::hasColumns('user_custom_profiles', ['title', 'description',])) {
            Schema::table('user_custom_profiles', function (Blueprint $table) {
                $table->dropColumn(['title', 'description',]);
            });
        }
    }

    protected function handleInsertStructure(): void
    {
        if (Structure::query()->exists()) {
            return;
        }

        $profile = Profile::query()
            ->where('user_type', CustomField::SECTION_TYPE_USER)
            ->first();

        if (!$profile instanceof Profile) {
            return;
        }

        $sectionIds = Section::query()->get()->map(function ($item) use ($profile) {
            return ['section_id' => $item->id, 'profile_id' => $profile?->entityId()];
        })->toArray();

        Structure::query()->insert($sectionIds);
    }
};
