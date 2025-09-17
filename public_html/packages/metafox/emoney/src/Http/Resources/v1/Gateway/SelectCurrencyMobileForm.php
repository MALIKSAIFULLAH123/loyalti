<?php

namespace MetaFox\EMoney\Http\Resources\v1\Gateway;

use MetaFox\Form\Mobile\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\SEO\ActionMeta;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SelectCurrencyMobileForm
 * @ignore
 * @codeCoverageIgnore
 */
class SelectCurrencyMobileForm extends SelectCurrencyForm
{
    public function boot(): void
    {
        $actionMeta = new ActionMeta();

        $actionMeta->continueAction()->type(MetaFoxConstant::TYPE_FORM_SCHEMA);

        $meta = $actionMeta->toArray();

        $this->setMultiStepFormMeta($meta);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $options = $this->getOptions();

        if (!count($options)) {
            $this->addHeader(['showRightHeader' => false])
                ->component('FormHeader');

            $basic->addFields(
                Builder::typography('description')
                    ->plainText(__p('ewallet::phrase.there_are_no_balances_available')),
            );

            return;
        }

        $basic->addFields(
            Builder::radioGroup('payment_gateway_balance_currency')
                ->required()
                ->label(__p('ewallet::phrase.choose_an_amount_to_pay'))
                ->options($options)
                ->yup(Yup::string()->required())
        );

        $this->addMoreBasicFields($basic);
    }
}
