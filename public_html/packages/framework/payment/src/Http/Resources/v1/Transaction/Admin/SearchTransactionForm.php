<?php
namespace MetaFox\Payment\Http\Resources\v1\Transaction\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;

class SearchTransactionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/payment/transaction')
            ->noHeader()
            ->acceptPageParams(['gateway_id', 'status'])
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
                Builder::choice('status')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.status'))
                    ->options($this->getStatusOptions()),
                Builder::text('gateway_transaction_id')
                    ->forAdminSearchForm()
                    ->label(__p('payment::admin.transaction_id')),
                Builder::submit()
                    ->forAdminSearchForm(),
                Builder::clearSearchForm()
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.reset'))
                    ->align('center')
                    ->sizeMedium(),
            );
    }

    private function getStatusOptions(): array
    {
        return app('payment.support')->getTransactionStatusOptions();
    }

    private function getGatewayOptions(): array
    {
        return resolve(GatewayRepositoryInterface::class)->getGatewaySearchOptions();
    }
}
