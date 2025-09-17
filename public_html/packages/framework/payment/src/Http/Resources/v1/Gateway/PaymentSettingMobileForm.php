<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;

class PaymentSettingMobileForm extends AbstractForm
{
    public function boot(): void
    {
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $values  = app('events')->dispatch('payment.account.setting_values', [$context]);

        if (is_array($values)) {
            $values = array_filter($values, function ($value) {
                return is_array($value);
            });
            $values = array_merge(...$values);
        }

        $this->action('payment-gateway/configuration-multiple')
            ->title(__p('payment::web.payment_settings'))
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $options = resolve(GatewayRepositoryInterface::class)->getActiveGateways();

        $fields = [];
        foreach ($options as $option) {
            $data = app('events')->dispatch('payment.account.setting', [$option->service], true);
            if (!is_array($data) || count($data) === 0) {
                continue;
            }
            $fields[] = $data;
        }

        if (count($fields) === 0) {
            $this->addBasic()->addField(
                Builder::typography('warning_message')
                    ->plainText(__p('payment::web.no_payment_options_available'))
            );

            return;
        }

        foreach ($fields as $field) {
            $basic = $this->addBasic(['label' => $field['label']]);
            $basic->addFields(...$field['fields']);
        }
    }
}
