<?php
namespace MetaFox\Payment\Traits\Request;

use MetaFox\Payment\Support\Facades\Payment;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * @property  InputBag $request
 */
trait HandlePaymentRequestTrait
{
    protected function getAdditionalPaymentGatewayRules(): ?array
    {
        $gatewayId = $this->request->get($this->getPaymentGatewayFieldName());

        if (!is_numeric($gatewayId)) {
            return null;
        }

        $context = user();

        return Payment::getPaymentValidationRules($context, $gatewayId, $this->request->all());
    }

    protected function getPaymentGatewayFieldName(): string
    {
        return 'payment_gateway';
    }
}
