<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Core\Support\Facades\Currency;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditCurrencyMobileForm.
 * @property ?User $resource
 */
class EditCurrencyMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     */
    public function boot(): void
    {
        $this->resource = user();
    }

    protected function prepare(): void
    {
        $profile = $this->resource instanceof User ? $this->resource->profile : null;

        $this
            ->title(__p('core::phrase.currency'))
            ->asPut()
            ->action('/account/setting')
            ->setValue([
                'currency_id' => $profile?->currency_id,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addField(
            Builder::choice('currency_id')
                ->required()
                ->label(__p('core::phrase.preferred_currency'))
                ->options(Currency::getActiveOptions())
                ->yup(Yup::string()->required()),
        );
    }
}
