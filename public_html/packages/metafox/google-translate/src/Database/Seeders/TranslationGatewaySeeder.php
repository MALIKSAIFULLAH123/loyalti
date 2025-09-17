<?php

namespace MetaFox\GoogleTranslate\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Translation\Repositories\TranslationGatewayRepositoryInterface;

/**
 * stub: packages/database/seeder-database.stub.
 */

/**
 * Class PackageSeeder.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class TranslationGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gateway = [
            [
                'service'     => 'googletranslate',
                'module_id'   => 'googletranslate',
                'is_active'   => 1,
                'title'       => 'Google Translate',
                'description' => 'Google Translate Gateway',
                'config'      => [
                    'api_key' => env('MFOX_GOOGLE_MAP_API_KEY', ''),
                ],
                'service_class' => \MetaFox\GoogleTranslate\Support\GoogleTranslate::class,
            ],
        ];

        resolve(TranslationGatewayRepositoryInterface::class)->setupTranslationGateways($gateway);
    }
}
