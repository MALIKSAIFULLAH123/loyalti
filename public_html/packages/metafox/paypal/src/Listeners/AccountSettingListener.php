<?php

namespace MetaFox\Paypal\Listeners;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Yup\Yup;

class AccountSettingListener
{
    public function handle(string $gatewayId): ?array
    {
        if ($gatewayId !== 'paypal') {
            return null;
        }

        $label = __p('paypal::phrase.paypal');

        return [
            'label'  => $label,
            'fields' => [
                Builder::text('paypal.value.merchant_id')
                    ->label(__p(('paypal::phrase.merchant_id')))
                    ->yup(
                        Yup::string()
                            ->nullable(),
                    ),
            ],
        ];
    }
}
