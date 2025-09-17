<?php

namespace MetaFox\User\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\User\Models\UserGender;

/**
 * Class UserGenderTableSeeder.
 * @codeCoverageIgnore
 * @ignore
 */
class UserGenderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(UserGender::query()->exists()){
            return;
        }
        $data = [
            [
                'phrase' => 'user::phrase.male',
                'is_custom' => 0,
            ],
            [
                'phrase' => 'user::phrase.female',
                'is_custom' => 0,
            ],
        ];

        UserGender::query()->insert($data);
    }
}
