<?php
namespace MetaFox\Payment\Traits\Controller;

use Illuminate\Support\Arr;
use MetaFox\Payment\Contracts\GatewayManagerInterface;
use MetaFox\Platform\Contracts\User;

trait HandleExtraPaymentParamsTrait
{
    public function getExtraPaymentParams(User $context, array $data): array
    {
        $gatewayId = Arr::get($data, $this->getPaymentGatewayFieldName());

        if (!is_numeric($gatewayId)) {
            return [];
        }

        $service = $this->getGatewayManagerService()->getGatewayServiceById($gatewayId);

        return $service->getExtraPaymentParams($context, $data);
    }

    protected function getGatewayManagerService(): GatewayManagerInterface
    {
        return resolve(GatewayManagerInterface::class);
    }

    protected function getPaymentGatewayFieldName(): string
    {
        return 'payment_gateway';
    }
}
