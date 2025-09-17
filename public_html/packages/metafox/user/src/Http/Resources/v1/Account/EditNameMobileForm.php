<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditNameMobileForm.
 * @property ?User $resource
 */
class EditNameMobileForm extends AbstractForm
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
        $value = $this->resource ? [
            'first_name' => $this->resource->first_name,
            'last_name'  => $this->resource->last_name,
            'full_name'  => $this->resource->full_name_raw,
        ] : null;
        $this
            ->title(__p('user::phrase.display_name'))
            ->action(url_utility()->makeApiUrl('/account/setting'))
            ->secondAction('editUserAccount/DONE')
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $this->handleFullNameField($basic);
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

        $basic->addField(
            Builder::text('full_name')
                ->label(__p('user::phrase.display_name'))
                ->placeholder(__p('user::phrase.display_name'))
                ->yup($fullNameYup),
        );
    }
}
