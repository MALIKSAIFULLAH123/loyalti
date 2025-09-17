<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Platform\PackageManager;

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
        if (!Schema::hasTable('core_country_states')) {
            return;
        }

        if (!CountryChild::query()->exists()) {
            return;
        }

        CountryChild::withoutEvents(function () {
            $statesMapping = PackageManager::readFile('metafox/localize', 'resources/states/VN.php');

            if (empty($statesMapping)) {
                return;
            }

            CountryChild::where('country_iso', 'VN')
                ->get()
                ->each(function (CountryChild $countryChild) use ($statesMapping) {
                    if (Arr::has($statesMapping, $countryChild->state_iso)) {
                        $countryChild->update([
                            'name' => Arr::get($statesMapping, $countryChild->state_iso)
                        ]);
                    }
                });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {}
};
