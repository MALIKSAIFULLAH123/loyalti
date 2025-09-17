<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use MetaFox\Subscription\Models\SubscriptionPackage as Model;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
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
class PaymentSubscriptionPackageForm extends GatewayForm
{
    /**
     * @var bool|null
     */
    protected ?bool $isFree = false;

    /**
     * @var bool|null
     */
    protected ?bool $isRecurring = false;

    /**
     * @var array|null
     */
    protected ?array $steps = null;

    /**
     * @var string|null
     */
    protected ?string $renewType = null;

    protected ?string $previousProcessChildId = 'subscription_get_gateway_form';
    protected ?string $previousCustomAction   = 'subscription_get_renew_form';

    /**
     * @param                                           $resource
     *
     * @throws AuthenticationException
     */
    public function __construct($resource = null)
    {
        parent::__construct($resource);

        $this->isFree = SubscriptionPackage::isFreePackageForUser(user(), $resource);
    }

    protected function prepare(): void
    {
        parent::prepare();

        if ($this->isFree) {
            $this->setAttribute('loadedAction', 'subscription/listing/reload');
            return;
        }

        $this->action('/subscription-invoice')
            ->asPost()
            ->secondAction('@redirectTo')
            ->setValue([
                'id'                        => $this->resource->entityId(),
                'previous_process_child_id' => $this->previousProcessChildId,
                'form_name'                 => 'subscription_payment_form',
            ]);
    }

    protected function initialize(): void
    {
        switch ($this->isFree) {
            case true:
                $this->addBasic()->addField(
                    Builder::description('free')
                        ->label(__p('subscription::phrase.your_membership_has_successfully_been_upgraded'))
                );

                $this->setFooterFields();
                break;
            default:
                parent::initialize();

                $basic = $this->getSectionByName('basic');

                $basic->addField(
                    Builder::hidden('id')
                );

                break;
        }
    }

    protected function setFooterFields(): void
    {
        $footer = $this->addFooter();

        if (is_array($this->steps)) {
            $footer->setMultiStepDescription($this->steps);
        }

        switch ($this->isFree) {
            case true:
                $footer->addField(
                    Builder::cancelButton()
                        ->label(__p('subscription::phrase.close'))
                );
                break;
            default:
                if ($this->resource->is_recurring) {
                    $footer->addFields(
                        Builder::customButton()
                            ->label(__p('subscription::phrase.back'))
                            ->customAction([
                                'type'    => 'multiStepForm/previous',
                                'payload' => [
                                    'formName'               => 'subscription_payment_form',
                                    'previousProcessChildId' => $this->previousCustomAction,
                                ],
                            ]),
                    );
                } else {
                    $footer->addField(
                        Builder::cancelButton()
                    );
                }
                break;
        }
    }

    public function setSteps(array $steps): static
    {
        $this->steps = $steps;

        return $this;
    }

    protected function getGatewayParams(): array
    {
        $price = json_decode($this->resource->price, true);

        $userCurrency = app('currency')->getUserCurrencyId(user());

        return array_merge(parent::getGatewayParams(), [
            'entity'       => $this->resource?->entityType(),
            'price'        => Arr::get($price, $userCurrency, 0),
            'is_recurring' => $this->isRecurring,
        ]);
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

    public function setPreviousCustomAction(string $previousCustomAction): self
    {
        $this->previousCustomAction = $previousCustomAction;

        return $this;
    }
}
