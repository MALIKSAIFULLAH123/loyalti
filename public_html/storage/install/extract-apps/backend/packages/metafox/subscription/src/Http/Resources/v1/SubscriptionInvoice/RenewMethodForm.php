<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

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
 * Class RenewMethodForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class RenewMethodForm extends AbstractForm
{
    /**
     * @var string
     */
    protected string $actionType;

    /**
     * @var array
     */
    protected $renewMethods;

    /**
     * @var bool
     */
    protected $isMultiStep;

    /**
     * @var array|null
     */
    protected ?array $steps = null;

    public function __construct($resource = null, string $actionType = null)
    {
        parent::__construct($resource);

        $this->actionType   = $actionType;
        $this->isMultiStep  = $resource->is_recurring && $actionType == Helper::UPGRADE_FORM_ACTION;
        $this->renewMethods = SubscriptionPackage::getPackageRenewMethodOptions($resource->package->entityId());
    }

    protected function prepare(): void
    {
        $values = [
            'action_type' => $this->actionType,
            'renew_type'  => Helper::RENEW_TYPE_AUTO,
        ];

        if (count($this->renewMethods) == 1) {
            Arr::set($values, 'renew_type', Arr::get($this->renewMethods, '0.value'));
        }

        $this->title(__p('subscription::phrase.select_renew_type'))
            ->action('subscription-invoice/payment-form/' . $this->resource->entityId())
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
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
