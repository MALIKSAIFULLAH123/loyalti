<?php

namespace MetaFox\Paypal\Http\Resources\v1\Gateway;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\Yup\Yup;

class UserGatewayForm extends AbstractForm
{
    protected string $settingName = 'paypal_gateway_configuration';

    public function boot(int $id): void
    {
        $this->resource = resolve(UserRepositoryInterface::class)->find($id);

        $context = user();

        policy_authorize(UserPolicy::class, 'updateSetting', $context, $this->resource);
    }

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

        $this->action('payment-gateway/configuration/:id')
            ->asPut()
            ->setValue($values);
    }

    protected function getSettingValues(): ?array
    {
        $values = app('events')->dispatch('payment.user.configuration', [$this->resource->entityId(), 'paypal'], true);

        if (null === $values) {
            return null;
        }

        return $values;
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::text('value.merchant_id')
                ->label(__p(('paypal::phrase.merchant_id')))
                ->description(__p('paypal::phrase.merchant_id_description', [
                    'merchant_link' => 'https://www.paypal.com/businessmanage/account/aboutBusiness',
                    'upgrade_link'  => 'https://www.paypal.com/bizsignup'
                ]))
                ->yup(
                    Yup::string()
                        ->nullable(),
                ),
        );

        $this->addDefaultFooter(true);
    }
}
