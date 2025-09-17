<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class PaymentSubscriptionPackageForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class PaymentSubscriptionInvoiceForm extends GatewayForm
{
    /**
     * @var
     */
    protected ?bool $isRecurring = false;

    /**
     * @var string
     */
    protected $customUrl;

    /**
     * @var string
     */
    protected $actionType;

    /**
     * @var bool
     */
    protected $isMultiStep;

    /**
     * @var array|null
     */
    protected ?array $steps = null;

    /**
     * @var string|null
     */
    protected ?string $renewType = null;

    protected ?string $previousProcessChildId = 'subscription_invoice_get_gateway_form';
    protected ?string $previousCustomAction   = 'subscription_invoice_get_renew_form';

    /**
     * @param $resource
     */
    public function __construct($resource = null, string $actionType = null)
    {
        parent::__construct($resource);

        $this->actionType = $actionType;

        $this->isMultiStep = $resource->is_recurring && $actionType == Helper::UPGRADE_FORM_ACTION;
    }

    public function setPreviousCustomAction(?string $previousCustomAction): void
    {
        $this->previousCustomAction = $previousCustomAction;
    }

    protected function prepare(): void
    {
        parent::prepare();

        $values = [
            'action_type'               => $this->actionType,
            'previous_process_child_id' => $this->previousProcessChildId,
            'form_name'                 => 'subscription_invoice_payment_form',
        ];

        if ($this->isMultiStep) {
            Arr::set($values, 'id', $this->resource->entityId());
        }

        $this->action('/subscription-invoice/upgrade/' . $this->resource->entityId())
            ->asPatch()
            ->secondAction('@redirectTo')
            ->setValue($values);
    }

    protected function initialize(): void
    {
        parent::initialize();

        $basic = $this->getSectionByName('basic');

        $basic->addField(
            Builder::hidden('id')
        );

        if (null !== $this->actionType) {
            $basic->addField(
                Builder::hidden('action_type')
            );
        }
    }

    protected function setFooterFields(): void
    {
        $footer = $this->addFooter();

        if (is_array($this->steps)) {
            $footer->setMultiStepDescription($this->steps);
        }

        if ($this->isMultiStep) {
            $footer->addFields(
                Builder::customButton()
                    ->label(__p('subscription::phrase.back'))
                    ->customAction([
                        'type'    => 'multiStepForm/previous',
                        'payload' => [
                            'formName'               => 'subscription_invoice_payment_form',
                            'previousProcessChildId' => $this->previousCustomAction,
                        ],
                    ]),
            );
        } else {
            $footer->addField(
                Builder::cancelButton()
            );
        }
    }

    public function setSteps(array $steps): static
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * TODO: Remove after supporting recurring in E-Wallet
     *
     * @return array|mixed[]
     * @throws AuthenticationException
     */
    protected function getGatewayOptions(): array
    {
        $options = parent::getGatewayOptions();

        if (!count($options)) {
            return [];
        }

        if (null === $this->renewType) {
            return $options;
        }

        if (Helper::RENEW_TYPE_MANUAL == $this->renewType) {
            return $options;
        }

        foreach ($options as $key => $option) {
            if (!$option instanceof AbstractField) {
                continue;
            }

            if ($option->getAttribute('name') != 'ewallet') {
                continue;
            }

            unset($options[$key]);
        }

        return array_values($options);
    }

    protected function getGatewayParams(): array
    {
        $price = $this->resource->initial_price;

        if (resolve(SubscriptionInvoiceRepositoryInterface::class)->hasCompletedTransactions($this->resource->entityId())) {
            $price = $this->resource->recurring_price;
        }

        return array_merge(parent::getGatewayParams(), [
            'entity'       => $this->resource->entityType(),
            'price'        => $price,
            'is_recurring' => $this->isRecurring,
        ]);
    }

    /**
     * @param bool|null $isRecurring
     *
     * @return $this
     */
    public function setIsRecurring(?bool $isRecurring): self
    {
        $this->isRecurring = $isRecurring;

        return $this;
    }

    /**
     * @param string $renewType
     *
     * @return $this
     */
    public function setRenewType(string $renewType): self
    {
        $this->renewType = $renewType;

        return $this;
    }

    public function setPreviousProcessChildId(string $previousProcessChildId): self
    {
        $this->previousProcessChildId = $previousProcessChildId;

        return $this;
    }
}
