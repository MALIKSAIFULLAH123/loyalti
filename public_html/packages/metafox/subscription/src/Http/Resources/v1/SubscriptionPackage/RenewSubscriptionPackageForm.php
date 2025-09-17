<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Subscription\Models\SubscriptionInvoice as Model;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use MetaFox\Subscription\Support\Helper;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class RenewSubscriptionInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class RenewSubscriptionPackageForm extends AbstractForm
{
    /**
     * @var ?array
     */
    protected ?array $renewMethods;

    /**
     * @var array|null
     */
    protected ?array $steps = null;

    public function __construct($resource = null)
    {
        parent::__construct($resource);

        $this->renewMethods = SubscriptionPackage::getPackageRenewMethodOptions($resource->entityId());
    }

    protected function prepare(): void
    {
        $values = [
            'id'                        => $this->resource->entityId(),
            'renew_type'                => Helper::RENEW_TYPE_AUTO,
            'previous_process_child_id' => 'subscription_get_gateway_form',
            'form_name'                 => 'subscription_payment_form',
        ];

        if (count($this->renewMethods) == 1) {
            Arr::set($values, 'renew_type', Arr::get($this->renewMethods, '0.value'));
        }

        $this->title(__p('subscription::phrase.select_renew_type'))
            ->action('subscription-package/payment-form/' . $this->resource->entityId())
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::hidden('id'),
            Builder::description('description')
                ->label(__p('subscription::phrase.select_type_for_renewing_your_subscription')),
            Builder::radioGroup('renew_type')
                ->options($this->renewMethods)
                ->yup(
                    Yup::string()
                        ->required()
                        ->setError('required', __p('subscription::validation.choose_one_method_to_purchase_recurring_subscription'))
                        ->setError('typeError', __p('subscription::validation.choose_one_method_to_purchase_recurring_subscription'))
                ),
        );

        $footer = $this->addFooter();

        if (is_array($this->steps)) {
            $footer->setMultiStepDescription($this->steps);
        }

        $footer->addFields(
            Builder::submit()
                ->label(__p('subscription::phrase.next')),
            Builder::cancelButton(),
        );
    }

    public function setSteps(array $steps): static
    {
        $this->steps = $steps;

        return $this;
    }
}
