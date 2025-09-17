<?php

namespace MetaFox\EMoney\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\EMoney\Contracts\CurrencyConverterInterface;
use MetaFox\EMoney\Models\ConversionRate;
use MetaFox\EMoney\Models\CurrencyConverter;
use MetaFox\EMoney\Models\WithdrawMethod;
use MetaFox\EMoney\Providers\CurrencyConversionRate\Visa;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\EMoney\Support\Support;

/**
 * stub: packages/database/seeder-database.stub.
 */

/**
 * Class PackageSeeder.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedingCurrencyConverters();
        $this->seedingConversionRates();
        $this->seedingWithdrawMethods();
    }

    protected function seedingWithdrawMethods(): void
    {
        $withdrawMethods = config('payment.withdraw_methods', []);

        if (!is_array($withdrawMethods)) {
            return;
        }

        WithdrawMethod::query()->upsert($withdrawMethods, ['service'], ['title', 'description', 'service_class', 'module_id']);
    }

    public function seedingConversionRates(): void
    {
        $count = ConversionRate::query()
            ->count();

        if ($count) {
            return;
        }

        resolve(ConversionRateServiceInterface::class)->findMissing(Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE);
    }

    protected function seedingCurrencyConverters(): void
    {
        $count = CurrencyConverter::query()->count();

        if ($count) {
            return;
        }

        /**
         * @var CurrencyConverterInterface $provider
         */
        $provider = resolve(Visa::class);

        $default = new CurrencyConverter([
            'service'       => $provider->getServiceName(),
            'service_class' => Visa::class,
            'title'         => $provider->getTitle(),
            'link'          => $provider->getInformationLink(),
            'is_default'    => true,
        ]);

        $default->save();
    }
}
