<?php

namespace MetaFox\Localize\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Localize\Models\Language;

/**
 * Class LanguageTableSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // skip
        if(Language::query()->exists()){
            return;
        }

        $data = [
            [
                'language_code' => 'en',
                'name'          => 'English',
                'charset'       => 'utf-8',
                'direction'     => 'ltr',
                'is_default'    => 1,
                'is_active'     => 1,
                'is_master'     => 1,
                'store_id'      => 0,
            ],
        ];

        foreach ($data as $item) {
            Language::firstOrCreate($item, $item);
        }
    }
}
