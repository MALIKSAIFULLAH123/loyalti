<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Http\Resources\v1;

use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        return [
            'has_buyer_configurable_payment_gateways' => $this->hasBuyerConfigurablePaymentGateways(),
            'payment_gateways' => $this->getSellerConfigurablePaymentGatewayServices(),
            'buyer_configurable_payment_gateways' => $this->getBuyerConfigurablePaymentGatewayServices(),
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'has_buyer_configurable_payment_gateways' => $this->hasBuyerConfigurablePaymentGateways(),
            'buyer_configurable_payment_gateways'     => $this->getBuyerConfigurablePaymentGatewayServices(),
        ];
    }

    protected function hasBuyerConfigurablePaymentGateways(): bool
    {
        return count($this->getBuyerConfigurablePaymentGatewayServices()) > 0;
    }

    protected function getBuyerConfigurablePaymentGatewayServices(): array
    {
        return LoadReduce::remember('payment::setting::getBuyerConfigurablePaymentGatewayServices', function () {
            $paymentGateways = $this->getBuyerConfigurablePaymentGateways();

            return collect($paymentGateways)
                ->keyBy('service')
                ->map(function () {
                    return true;
                })
                ->toArray();
        });
    }

    protected function getSellerConfigurablePaymentGatewayServices(): array
    {
        $paymentGateways = $this->getSellerConfigurablePaymentGateways();

        return array_map(function ($paymentGateway) {
            return $paymentGateway['service'];
        }, $paymentGateways);
    }

    /**
     * @return array
     */
    protected function getBuyerConfigurablePaymentGateways(): array
    {
        return $this->gatewayRepository()
            ->getBuyerConfigurableGateways()
            ->toArray();
    }

    /**
     * @return array
     */
    protected function getSellerConfigurablePaymentGateways(): array
    {
        return $this->gatewayRepository()
            ->getConfigurationGateways()
            ->toArray();
    }

    /**
     * @return GatewayRepositoryInterface
     */
    protected function gatewayRepository(): GatewayRepositoryInterface
    {
        return resolve(GatewayRepositoryInterface::class);
    }
}
