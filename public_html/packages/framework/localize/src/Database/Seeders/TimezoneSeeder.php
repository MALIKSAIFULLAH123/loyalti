<?php

namespace MetaFox\Localize\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Localize\Models\Timezone;

/**
 * Class TimezoneSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (Timezone::query()->exists()) {
            return;
        }

        $timestamp = time();
        $timezones = [];
        foreach (timezone_identifiers_list() as $zone) {
            date_default_timezone_set($zone);
            $timezone = [];
            $timezone['name'] = $zone;
            $timezone['offset'] = date('P', $timestamp);
            $timezone['diff_from_gtm'] = 'GMT '.$timezone['offset'];
            $timezones[] = $timezone;
        }

        Timezone::query()->insert($timezones);
    }
}
