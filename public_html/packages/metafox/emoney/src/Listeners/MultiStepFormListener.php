<?php

namespace MetaFox\EMoney\Listeners;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Http\Resources\v1\Gateway\SelectCurrencyForm;
use MetaFox\EMoney\Http\Resources\v1\Gateway\SelectCurrencyMobileForm;
use MetaFox\EMoney\Support\Gateway\EwalletPaymentGateway;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Constants;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\MetaFox;

class MultiStepFormListener
{
    public function handle(Gateway $gateway, string $actionUrl, array $formValues): ?AbstractForm
    {
        if ($gateway->service != EwalletPaymentGateway::GATEWAY_SERVICE_NAME) {
            return null;
        }

        $context = user();
        $previousProcessChildId = Arr::pull($formValues, 'previous_process_child_id');
        $formName               = Arr::pull($formValues, 'form_name');
        $price                  = Arr::pull($formValues, 'price');
        $currencyId             = Arr::pull($formValues, 'currency_id');
        $method                 = Arr::pull($formValues, 'method',Constants::METHOD_POST);

        return resolve(EwalletPaymentGateway::class)->getNextPaymentForm($context, $formValues, [
            'previous_process_child_id' => $previousProcessChildId,
            'form_name'                => $formName,
            'price'                    => $price,
            'currency_id'              => $currencyId,
            'method'                   => $method,
            'action_url'               => $actionUrl,
        ]);
    }
}
