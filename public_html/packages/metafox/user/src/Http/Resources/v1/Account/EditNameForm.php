<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditNameForm.
 * @property ?User $resource
 */
class EditNameForm extends AbstractForm
{
    public function boot(): void
    {
        $this->resource = user();
    }

    protected function prepare(): void
    {
        $value = $this->resource ? [
            'first_name' => $this->resource->first_name,
            'last_name'  => $this->resource->last_name,
            'full_name'  => $this->resource->full_name_raw,
        ] : null;
        $this
            ->action(url_utility()->makeApiUrl('/account/setting'))
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->handleFullNameField($basic);

        $footer = $this->addFooter(['separator' => false]);

        $footer->addFields(
            Builder::submit()->label(__p('core::phrase.save'))->variant('contained'),
            Builder::cancelButton()->label(__p('core::phrase.cancel'))->variant('outlined'),
        );
    }

    protected function handleFullNameField(Section $basic): void
    {
        $fullNameYup = Yup::string()
            ->setError('typeError', __p('validation.full_name_is_a_required_field'));

        if (Settings::get('user.validate_full_name', true)) {
            $displayNameRegex = Regex::getRegexSetting('display_name');

            $fullNameYup->maxLength(Settings::get('user.maximum_length_for_full_name'))
                ->minLength(3)
                ->matches($displayNameRegex, __p(Settings::get('regex.display_name_regex_error_message')));
        }

        $basic->addField(Builder::text('full_name')
            ->label(__p('user::phrase.display_name'))
            ->placeholder(__p('user::phrase.display_name'))
            ->marginNormal()
            ->variant('outlined')
            ->yup($fullNameYup));
    }
}
