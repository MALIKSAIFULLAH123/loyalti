<?php

namespace MetaFox\Paypal\Support\Withdraws;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Providers\Withdraw\AbstractWithdrawMethod;
use MetaFox\EMoney\Support\Support;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

class Paypal extends AbstractWithdrawMethod
{
    public function placeOrder(User $payee, WithdrawRequest $request, array $params = []): ?array
    {
        if (!$this->validateGateway($payee, $request->currency)) {
            return null;
        }

        $gateway = resolve(GatewayRepositoryInterface::class)->getGatewayByService('paypal');

        if (null === $gateway || !$gateway->is_active) {
            return null;
        }

        $order = Payment::initOrder($request);

        if (null === $order) {
            return null;
        }

        $url = $request->toUrl();

        $paymentParams = [
            'return_url' => Arr::get($params, 'return_url', $url),
            'cancel_url' => Arr::get($params, 'cancel_url', $url),
        ];

        if (Arr::has($params, 'description')) {
            Arr::set($paymentParams, 'description', Arr::get($params, 'description'));
        }

        return Payment::placePayeeOrder($order, $gateway->entityId(), $payee->entityId(), $paymentParams);
    }

    public function validateGateway(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): bool
    {
        $gateway = resolve(GatewayRepositoryInterface::class)->getGatewayByService('paypal');

        if (null === $gateway || !$gateway->is_active) {
            return false;
        }

        $result = app('events')->dispatch('payment.user_configuration.has_access', [$user->entityId(), $gateway->entityId()], true);

        if (!$result) {
            return false;
        }

        return true;
    }
}
