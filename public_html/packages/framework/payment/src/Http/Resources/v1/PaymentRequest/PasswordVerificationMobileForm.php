<?php
namespace MetaFox\Payment\Http\Resources\v1\PaymentRequest;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;
use MetaFox\Yup\Yup;

class PasswordVerificationMobileForm extends MobileForm
{
    /**
     * @var string|null
     */
    protected ?string $previousProcessChildId = null;

    /**
     * @var string|null
     */
    protected ?string $processChildId = null;

    /**
     * @var string|null
     */
    protected ?string $formName = null;

    /**
     * @var array
     */
    protected array $paymentParams = [];

    /**
     * @var array
     */
    protected array $gatewayInfo = [];

    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.confirm_your_payment'));

        if (!$this->getAttribute('method')) {
            $this->asPost();
        }
    }

    protected function addOrderDetail(?Section $section = null): void
    {
        $gatewayParams = $this->getPaymentParams();

        if (!count($gatewayParams)) {
            return;
        }

        if (null === $section) {
            $section = $this->addSection('order_detail');
        }

        $orderDetail = Arr::get($gatewayParams, 'order_detail_info');

        if (!is_array($orderDetail) || !count($orderDetail)) {
            return;
        }

        $first = array_shift($orderDetail);

        $section->label(__p('payment::phrase.your_order'))
            ->addFields(
                $this->getOrderItemTitleField($first),
                Builder::typography('order_item_price')
                    ->plainText($this->getOrderItemPriceDescription($first)),
            );
    }

    protected function getOrderItemTitleField(array $item): ?AbstractField
    {
        $title = Arr::get($item, 'title');

        if (!is_string($title) || MetaFoxConstant::EMPTY_STRING === $title) {
            return null;
        }

        return Builder::typography('order_item_title')
            ->plainText($this->getOrderItemTitleDescription($item));
    }

    protected function getOrderItemTitleDescription(array $item): string
    {
        $link = Arr::get($item, 'link');

        return __p('payment::phrase.order_item_title', [
            'hasLink' => is_string($link) ? 1 : 0,
            'title'   => Arr::get($item, 'title'),
            'link'    => $link,
        ]);
    }

    protected function getOrderItemPriceDescription(array $item): string
    {
        $formatted = app('currency')->getPriceFormatByCurrencyId(Arr::get($item, 'currency'), Arr::get($item, 'price'));

        return __p('payment::phrase.order_item_price', ['price' => $formatted]);
    }

    protected function addGatewayDetail(?Section $section = null): void
    {
        $gatewayInfo = $this->getGatewayInfo();

        if (!count($gatewayInfo)) {
            return;
        }

        if (null === $section) {
            $section = $this->addSection('gateway_detail');
        }

        $section->label(__p('payment::phrase.payment_with_gateway_title', ['title' => Arr::get($gatewayInfo, 'title')]))
            ->addFields(
                Builder::typography('card_number')
                    ->plainText(__p('payment::phrase.card_number', ['number' => Arr::get($gatewayInfo, 'card_number')])),
                Builder::typography('card_type')
                    ->plainText(__p('payment::phrase.card_type', ['type' => Arr::get($gatewayInfo, 'card_type')])),
            );
    }

    protected function addBasicFields(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography('payment_verification_password_warning')
                ->plainText(__p('payment::phrase.verification_password_warning')),
            Builder::password('payment_verification_password')
                ->label(__p('user::phrase.your_password'))
                ->required()
                ->yup(
                    Yup::string()->required()
                ),
        );
    }

    protected function initialize(): void
    {
        $this->addOrderDetail();

        $this->addGatewayDetail();

        $this->addBasicFields();
    }

    /**
     * @param string|null $previousProcessChildId
     * @return $this
     */
    public function setPreviousProcessChildId(?string $previousProcessChildId): static
    {
        $this->previousProcessChildId = $previousProcessChildId;

        return $this;
    }

    /**
     * @return string|null
     */
    protected function getPreviousProcessChildId(): ?string
    {
        return $this->previousProcessChildId ?? 'select_payment_gateway_form';
    }

    /**
     * @param string|null $formName
     * @return $this
     */
    public function setFormName(?string $formName): static
    {
        $this->formName = $formName;

        return $this;
    }

    /**
     * @return string
     */
    protected function getFormName(): string
    {
        return $this->formName ?? 'before_payment_form';
    }

    /**
     * @param string|null $processChildId
     * @return $this
     */
    public function setProcessChildId(?string $processChildId): static
    {
        $this->processChildId = $processChildId;

        return $this;
    }

    /**
     * @return string
     */
    protected function getProcessChildId(): string
    {
        return $this->processChildId ?? 'verification_password';
    }

    /**
     * @param array $paymentParams
     * @return $this
     */
    public function setPaymentParams(array $paymentParams): static
    {
        $this->paymentParams = $paymentParams;

        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentParams(): array
    {
        return $this->paymentParams;
    }

    /**
     * @param array $gatewayInfo
     * @return $this
     */
    public function setGatewayInfo(array $gatewayInfo): static
    {
        $this->gatewayInfo = $gatewayInfo;

        return $this;
    }

    /**
     * @return array
     */
    public function getGatewayInfo(): array
    {
        return $this->gatewayInfo;
    }

    protected function getActionMeta(): ActionMeta
    {
        return (new ActionMeta())
            ->continueAction()
            ->type(MetaFoxConstant::TYPE_MULTISTEP_FORM_NEXT)
            ->payload(
                PayloadActionMeta::payload()
                    ->previousProcessChildId($this->getPreviousProcessChildId())
                    ->processChildId($this->getProcessChildId())
                    ->formName($this->getFormName())
            );
    }

    public function assignMultiStepFormMeta(): static
    {
	    return $this->setMultiStepFormMeta([
		    'continueAction' => [
			    'type' => 'formSchema',
		    ],
	    ]);
    }
}
