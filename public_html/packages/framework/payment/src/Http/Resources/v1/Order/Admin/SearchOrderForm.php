<?php
namespace MetaFox\Payment\Http\Resources\v1\Order\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;

class SearchOrderForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/payment/order')
            ->noHeader()
            ->acceptPageParams(['gateway_id', 'payment_type', 'status', 'recurring_status', 'gateway_order_id', 'gateway_subscription_id'])
            ->submitAction('@formAdmin/search/SUBMIT');
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::choice('gateway_id')
                    ->forAdminSearchForm()
                    ->label(__p('payment::admin.payment_gateway'))
                    ->options($this->getGatewayOptions()),
                Builder::choice('payment_type')
                    ->forAdminSearchForm()
                    ->label(__p('payment::admin.type'))
                    ->options($this->getTypeOptions()),
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->options($this->getStatusOptions()),
                Builder::choice('recurring_status')
                    ->forAdminSearchForm()
                    ->label(__p('payment::admin.subscription_status'))
                    ->options($this->getRecurringStatusOptions()),
                Builder::text('gateway_order_id')
                    ->forAdminSearchForm()
                    ->label(__p('payment::admin.order_id')),
                Builder::text('gateway_subscription_id')
                    ->forAdminSearchForm()
                    ->label(__p('payment::admin.subscription_id')),
                Builder::submit()
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center')
                    ->sizeMedium(),
            );
    }

    private function getRecurringStatusOptions(): array
    {
        $options = app('payment.support')->getRecurringStatusOptions();

        return array_values(array_filter($options, function ($option) {
            return Arr::get($option, 'value') != Order::STATUS_ALL;
        }));
    }

    private function getStatusOptions(): array
    {
        $options = app('payment.support')->getOrderStatusOptions();

        return array_values(array_filter($options, function ($option) {
            return Arr::get($option, 'value') != Order::STATUS_ALL;
        }));
    }

    private function getTypeOptions(): array
    {
        return app('payment.support')->getPaymentTypeOptions();
    }

    private function getGatewayOptions(): array
    {
        return resolve(GatewayRepositoryInterface::class)->getGatewaySearchOptions();
    }
}
