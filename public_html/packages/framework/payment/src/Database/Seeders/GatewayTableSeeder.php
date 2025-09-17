<?php

namespace MetaFox\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;

class GatewayTableSeeder extends Seeder
{
    public function __construct(
        protected GatewayRepositoryInterface $repository
    ) {
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkExist = Gateway::query()->first();

        if (null != $checkExist) {
            return;
        }

        $gatewayConfigs = config('payment.gateways', []);

        if (empty($gatewayConfigs)) {
            return;
        }

        $this->repository->setupPaymentGateways($gatewayConfigs);
    }
}
