<?php

namespace MetaFox\Payment\Http\Resources\v1\Order;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\MultiStepFormTrait;
use MetaFox\Form\Section;
use MetaFox\Payment\Contracts\GatewayManagerInterface;
use MetaFox\Payment\Models\Order as Model;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;
use MetaFox\SEO\PayloadActionMeta;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class GatewayForm.
 * @property ?Model $resource
 */
class GatewayForm extends AbstractForm
{
    use MultiStepFormTrait;

    protected function serviceManager(): GatewayManagerInterface
    {
        return resolve(GatewayManagerInterface::class);
    }

    public function boot(?int $id = null): void
    {
        if ($id) {
            $this->resource = resolve(OrderRepositoryInterface::class)->find($id);
        }

        if (!MetaFox::isMobile()){
            $actionMeta = $this->getActionMeta();

            $this->setMultiStepFormMeta($actionMeta->toArray());
        }
    }

    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.select_payment_gateway'))
            ->action('payment-gateway/order' . $this->resource?->id)
            ->asPut();
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->buildPaymentGatewayField($basic);

        $this->addMoreBasicFields($basic);

        $this->setFooterFields();
    }

    protected function addMoreBasicFields(Section $basic): void
    {
        /*
         * Extendable classes can implement here
         */
    }

    protected function setFooterFields(): void
    {
        $this->addFooter()
            ->addFields(
                Builder::cancelButton(),
            );
    }

    /**
     * @return array<int, mixed>
     * @throws AuthenticationException
     */
    protected function getGatewayOptions(): array
    {
        return $this->serviceManager()->getGatewaysForForm(user(), $this->getGatewayParams(), $this->resource);
    }

    protected function getGatewayParams(): array
    {
        return [
            'entity_type' => $this->resource?->entityType(),
            'entity_id'   => $this->resource?->entityId(),
        ];
    }

    protected function requiredGateway(): bool
    {
        return false;
    }

    /**
     * @return bool
     * @throws AuthenticationException
     */
    protected function hasPaymentGateway(): bool
    {
        return count($this->getGatewayOptions()) >= 1;
    }

    /**
     * @throws AuthenticationException
     */
    protected function buildPaymentGatewayField(Section $section): void
    {
        if (!$this->hasPaymentGateway()) {
            $section->addField(Builder::typography('no_payment_options')
                ->variant('h5')
                ->plainText(__p('core::phrase.no_payment_options_available')));

            return;
        }

        $fields = $this->getGatewayOptions();

        $section->addFields(...$fields);
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

    protected function getActionMeta():ActionMeta
    {
        $actionMeta = new ActionMeta();

        $actionMeta->continueAction()
            ->type(MetaFoxConstant::TYPE_MULTISTEP_FORM_NEXT)
            ->payload(PayloadActionMeta::payload()
                ->processChildId('select_payment_gateway_form')
                ->formName('before_payment_form'));

        return $actionMeta;
    }
}
