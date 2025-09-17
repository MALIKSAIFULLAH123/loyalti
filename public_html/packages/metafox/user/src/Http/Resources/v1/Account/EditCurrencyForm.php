<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditCurrencyForm.
 * @property ?User $resource
 */
class EditCurrencyForm extends AbstractForm
{
    public function boot(): void
    {
        $this->resource = user();
    }

    protected function prepare(): void
    {
        $value = $this->resource ? [
            'currency_id' => $this->resource->profile->currency_id,
        ] : null;

        $this
            ->asPut()
            ->action('/account/setting')
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::choice('currency_id')
                ->marginNormal()
                ->label(__p('core::phrase.preferred_currency'))
                ->placeholder(__p('user::phrase.preferred_currency'))
                ->options(app('currency')->getActiveOptions())
                ->required()
                ->yup(Yup::string()->required()),
        );

        $footer = $this->addFooter(['separator' => false]);

        $footer->addFields(
            Builder::submit()->label(__p('core::phrase.save'))->variant('contained'),
            Builder::cancelButton()->label(__p('core::phrase.cancel'))->variant('outlined'),
        );
    }
}
