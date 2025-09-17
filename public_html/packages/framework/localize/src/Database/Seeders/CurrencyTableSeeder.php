<?php

namespace MetaFox\Localize\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Localize\Models\Currency;

/**
 * Class CurrencyTableSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (Currency::query()->exists()) {
            return;
        }

        $data = config('currencies');

        foreach ($data as $item) {
            Currency::firstOrCreate($item, $item);
        }
    }
}
