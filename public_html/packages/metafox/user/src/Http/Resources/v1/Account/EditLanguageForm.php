<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditLanguageForm.
 * @property ?User $resource
 */
class EditLanguageForm extends AbstractForm
{
    public function boot(): void
    {
        $this->resource = user();
    }

    protected function prepare(): void
    {
        $value = $this->resource ? [
            'language_id' => $this->resource->profile->language_id,
        ] : null;
        $this
            ->asPut()
            ->action(url_utility()->makeApiUrl('/account/setting'))
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('language_id')
                ->marginNormal()
                ->label(__p('core::phrase.primary_language'))
                ->placeholder(__p('core::phrase.primary_language'))
                ->autoComplete('off')
                ->required()
                ->options(Language::getActiveOptions())
                ->yup(Yup::string()->required()),
        );

        $footer = $this->addFooter(['separator' => false]);

        $footer->addFields(
            Builder::submit()->label(__p('core::phrase.save'))->variant('contained'),
            Builder::cancelButton()->label(__p('core::phrase.cancel'))->variant('outlined'),
        );
    }
}
