<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Profile\Models\Profile;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Models\Structure;

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
        if (Schema::hasColumn('user_custom_sections', 'is_system')) {
            return;
        }

        Schema::table('user_custom_sections', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_system')->default(0);
        });

        $this->updateLabelMenu();
        $this->insertNewSection();
        // to do here
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('user_custom_sections', 'is_system')) {
            Schema::table('user_custom_sections', function (Blueprint $table) {
                $table->dropColumn('is_system');
            });
        }
    }

    protected function insertNewSection(): void
    {
        $profile = Profile::query()->where('user_type', 'user')->first();

        if (!$profile) {
            return;
        }

        Section::query()->upsert([
            [
                'name'      => 'basic_info',
                'is_active' => 1,
                'is_system' => 1,
            ],
        ], ['name']);

        $section = Section::query()->where('name', 'basic_info')->first();

        if (!$section) {
            return;
        }

        Structure::query()->insert([
            'section_id' => $section->id,
            'profile_id' => $profile->id,
        ]);
    }

    protected function updateLabelMenu(): void
    {
        MenuItem::query()->where([
            'menu' => 'profile.admin',
            'name' => 'customSection',
        ])->update([
            'label' => 'profile::phrase.manage_profile_sections',
        ]);
    }
};
