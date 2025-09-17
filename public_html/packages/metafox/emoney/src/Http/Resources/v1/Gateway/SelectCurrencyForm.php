<?php

namespace MetaFox\EMoney\Http\Resources\v1\Gateway;

use Illuminate\Support\Arr;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SelectCurrencyForm
 *
 * @ignore
 * @codeCoverageIgnore
 */
class SelectCurrencyForm extends AbstractForm
{
    public array   $values                 = [];
    public ?string $actionUrl              = null;
    public float   $price;
    public string  $currency_id;
    public ?string $previousProcessChildId = null;
    public ?string $formName               = null;
    public string  $method;

    protected function prepare(): void
    {
        $values  = $this->getValues();

        $options = $this->getOptions();

        $options = array_filter($options, function ($item) {
            return !$item['disabled'];
        });

        if (count($options) == 1) {
            $options = Arr::first($options);

            Arr::set($values, 'payment_gateway_balance_currency', Arr::get($options, 'value'));
        }

        $this->title(__p('ewallet::phrase.select_balance'))
            ->action($this->getActionUrl())
            ->secondAction('@redirectTo')
            ->method($this->getMethod())
            ->setValue($values);
    }

    public function boot(): void
    {
        $actionMeta = new ActionMeta();

        $actionMeta->continueAction()
            ->type(MetaFoxConstant::TYPE_MULTISTEP_FORM_NEXT)
            ->payload(PayloadActionMeta::payload()
                ->processChildId('select_currency_form')
                ->previousProcessChildId($this->getPreviousProcessChildId())
                ->formName($this->getFormName()));

        $meta = $actionMeta->toArray();

        $this->setMultiStepFormMeta($meta);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $options = $this->getOptions();

        if (!count($options)) {
            $basic->addFields(
                Builder::typography('description')
                    ->plainText(__p('ewallet::phrase.there_are_no_balances_available')),
            );

            $this->addFooter()->addFields(
                Builder::customButton()
                    ->label(__p('core::phrase.back'))
                    ->customAction([
                        'type'    => MetaFoxConstant::TYPE_MULTISTEP_FORM_PREVIOUS,
                        'payload' => [
                            'previousProcessChildId' => $this->getPreviousProcessChildId(),
                        ],
                    ]),
            );

            return;
        }

        $basic->addFields(
            Builder::radioGroup('payment_gateway_balance_currency')
                ->required()
                ->label(__p('ewallet::phrase.choose_an_amount_to_pay'))
                ->options($options)
                ->yup(Yup::string()->required(__p('ewallet::validation.please_choose_an_amount_to_pay')))
        );

        $this->addMoreBasicFields($basic);

        $this->addFooter()->addFields(
            Builder::submit()
                ->label(__p('core::web.buy_now')),
            Builder::customButton()
                ->label(__p('core::phrase.back'))
                ->customAction([
                    'type'    => MetaFoxConstant::TYPE_MULTISTEP_FORM_PREVIOUS,
                    'payload' => [
                        'previousProcessChildId' => $this->getPreviousProcessChildId(),
                    ],
                ]),
        );
    }

    protected function addMoreBasicFields(Section $basic): void
    {
        foreach ($this->getValues() as $key => $value) {
            $basic->addField(Builder::hidden($key));
        }
    }

    protected function getOptions(): array
    {
        $context  = user();
        $options  = $this->statisticRepository()->getUserBalancesOptions($context);
        $price    = $this->getPrice();
        $currency = $this->getCurrencyId();

        $items = array_map(function ($item) use ($price, $currency) {
            $isDisabled = false;

            if ($item['value'] != $currency) {
                $price = app('ewallet.conversion-rate')->getConversedAmount($currency, $price, $item['value']);
            }

            if (null === $price) {
                return null;
            }

            Arr::set($item, 'description', __p('ewallet::phrase.this_action_will_cost_you_money', [
                'price' => app('currency')->getPriceFormatByCurrencyId($item['value'], $price),
            ]));

            if ($item['total_balance'] < $price) {
                $isDisabled = true;
            }

            Arr::set($item, 'disabled', $isDisabled);

            if ($isDisabled) {
                Arr::set($item, 'description', __p('ewallet::phrase.currency_is_not_enough_for_payment', [
                    'currency' => $item['value'],
                ]));
            }

            return $item;
        }, $options);

        return array_values(array_filter($items, function ($item) {
            return is_array($item);
        }));
    }

    protected function statisticRepository(): StatisticRepositoryInterface
    {
        return resolve(StatisticRepositoryInterface::class);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function getActionUrl(): string
    {
        $id = Arr::get($this->getValues(), 'id');

        return $this->actionUrl ?? 'payment-gateway/order/' . $id;
    }

    public function setActionUrl(?string $actionUrl): self
    {
        $this->actionUrl = $actionUrl;

        return $this;
    }

    protected function addBasic(array $config = []): Section
    {
        $config = [
            'sx' => [
                'maxWidth' => '100%',
                'width'    => '400px',
            ],
        ];

        return parent::addBasic($config);
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getCurrencyId(): string
    {
        return $this->currency_id;
    }

    public function setCurrencyId(string $currency_id): void
    {
        $this->currency_id = $currency_id;
    }

    public function setPreviousProcessChildId(?string $previousProcessChildId): void
    {
        $this->previousProcessChildId = $previousProcessChildId;
    }

    public function getPreviousProcessChildId(): ?string
    {
        return $this->previousProcessChildId ?? 'select_payment_gateway_form';
    }

    public function setFormName(?string $formName): void
    {
        $this->formName = $formName;
    }

    public function getFormName(): ?string
    {
        return $this->formName ?? 'before_payment_form';
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
