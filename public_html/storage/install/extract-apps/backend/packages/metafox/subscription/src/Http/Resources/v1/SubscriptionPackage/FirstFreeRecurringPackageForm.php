<?php
namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage;

use MetaFox\Form\Builder;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;

class FirstFreeRecurringPackageForm extends GatewayForm
{
    protected function prepare(): void
    {
        parent::prepare();

        $this->setAttribute('loadedAction', 'subscription/listing/reload');
    }

    protected function initialize(): void
    {
        $this->addBasic()->addField(
            Builder::description('free')
                ->label(__p('subscription::phrase.your_membership_has_successfully_been_upgraded'))
        );

        $this->addFooter()
            ->addFields(
                Builder::cancelButton()
                    ->label(__p('subscription::phrase.close'))
            );
    }
}
