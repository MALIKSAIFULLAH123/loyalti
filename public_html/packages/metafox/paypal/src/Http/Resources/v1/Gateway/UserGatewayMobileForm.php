<?php

namespace MetaFox\Paypal\Http\Resources\v1\Gateway;

use Illuminate\Support\Arr;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Yup\Yup;

class UserGatewayMobileForm extends UserGatewayForm
{
    protected function prepare(): void
    {
        $values = [
            'gateway_id' => 0,
            'value'      => null,
        ];

        $settingValues = $this->getSettingValues();

        if (is_array($settingValues)) {
            $values = array_merge($values, [
                'value' => $settingValues,
            ]);
        }

        $gateway = app('events')->dispatch('payment.gateway.get', ['paypal'], true);

        if (null !== $gateway) {
            Arr::set($values, 'gateway_id', $gateway->entityId());
        }

        $this->title($gateway?->title ?? 'PayPal')
            ->action('payment-gateway/configuration/' . $this->resource->entityId())
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::text('value.merchant_id')
                ->label(__p(('paypal::phrase.merchant_id')))
                ->yup(
                    Yup::string()
                        ->nullable(),
                ),
        );
    }
}
