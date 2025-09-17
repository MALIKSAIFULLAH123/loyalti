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
        $this->createCountriesTable();
        $this->createGendersTable();

        $this->migrateGendersData();
        $this->migrateCountriesData();

        $this->dropColumns();
        // to do here
    }

    protected function createCountriesTable(): void
    {
        if (Schema::hasTable('announcement_country_data')) {
            return;
        }

        Schema::create('announcement_country_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->index();
            $table->string('country_iso')->index();
        });
    }


    protected function createGendersTable(): void
    {
        if (Schema::hasTable('announcement_gender_data')) {
            return;
        }

        Schema::create('announcement_gender_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->index();
            $table->integer('gender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_country_data');
        Schema::dropIfExists('announcement_gender_data');

        if (!Schema::hasColumns('announcements', ['country_iso', 'gender'])) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('country_iso', 10)->nullable();
                $table->unsignedInteger('gender')->nullable();
            });
        }
    }

    protected function migrateGendersData(): void
    {
        $model       = new \MetaFox\Announcement\Models\Announcement();
        $genderModel = new \MetaFox\Announcement\Models\GenderData();

        if (!Schema::hasColumns('announcements', ['gender'])) {
            return;
        }

        $query = $model->newModelQuery()
            ->select('id as item_id', 'gender as gender_id')
            ->whereNotNull('gender')->whereNot('gender', 0);

        $genderModel->newQuery()->insertUsing(['item_id', 'gender_id'], $query);
    }

    protected function migrateCountriesData(): void
    {
        $model        = new \MetaFox\Announcement\Models\Announcement();
        $countryModel = new \MetaFox\Announcement\Models\CountryData();

        if (!Schema::hasColumns('announcements', ['gender'])) {
            return;
        }

        $query = $model->newModelQuery()
            ->select('id as item_id', 'country_iso')
            ->whereNotNull('country_iso');

        $countryModel->newQuery()->insertUsing(['item_id', 'country_iso'], $query);
    }

    protected function dropColumns(): void
    {
        if (Schema::hasColumns('announcements', ['country_iso', 'gender'])) {
            Schema::dropColumns('announcements', ['country_iso', 'gender']);
        }
    }
};
