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
        $this->upCountries();

        $this->upStates();

        $this->upCities();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('core_countries');
        Schema::dropIfExists('core_country_states');
        Schema::dropIfExists('core_country_cities');
    }

    /**
     * @return void
     */
    private function upCountries(): void
    {
        if (Schema::hasTable('core_countries')) {
            return;
        }

        Schema::create('core_countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('country_iso', 2)->unique();

            $table->char('code', 2)->nullable();
            $table->char('code3', 3)->nullable();

            $table->char('numeric_code', 3)->nullable();
            $table->bigInteger('geonames_code')->nullable();
            $table->char('fips_code', 10)->nullable();

            $table->char('area', 10)->nullable();
            $table->char('currency', 5)->nullable();
            $table->string('phone_prefix')->nullable();
            $table->string('mobile_format')->nullable();
            $table->string('landline_format')->nullable();
            $table->string('trunk_prefix')->nullable();
            $table->bigInteger('population')->nullable();
            $table->char('continent', 10)->nullable();
            $table->char('language', 10)->nullable();

            $table->string('name')->index();

            $table->unsignedSmallInteger('ordering')->default(0);
            $table->unsignedTinyInteger('is_active')->default(1);
        });
    }

    /**
     * @return void
     */
    private function upStates(): void
    {
        if (Schema::hasTable('core_country_states')) {
            return;
        }
        Schema::create('core_country_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('country_iso', 2)->nullable()->index();
            $table->string('state_iso', 10)->nullable()->index();

            $table->bigInteger('state_code')->nullable()->index();

            $table->bigInteger('geonames_code')->nullable();
            $table->string('fips_code', 10)->nullable();
            $table->text('post_codes')->nullable();
            $table->string('timezone')->nullable();

            $table->string('name')->index();
            $table->unsignedSmallInteger('ordering')->nullable()->default(0);
        });
    }

    /**
     * @return void
     */
    private function upCities(): void
    {
        if (Schema::hasTable('core_country_cities')) {
            return;
        }
        Schema::create('core_country_cities', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('state_code')->index();
            $table->bigInteger('city_code')->index();

            $table->bigInteger('geonames_code')->nullable();

            $table->decimal('latitude', 30, 2)->nullable();
            $table->decimal('longitude', 30, 2)->nullable();
            $table->string('capital')->nullable();

            $table->bigInteger('population')->nullable();

            $table->string('name')->index();
            $table->unsignedSmallInteger('ordering')->default(0);
        });
    }
};
