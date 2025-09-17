<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\User\Models\User;
use MetaFox\Yup\Yup;

/**
 * Class EditUserNameMobileForm.
 * @property ?User $resource
 */
class EditUserNameMobileForm extends AbstractForm
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
            'user_name' => $this->resource->user_name,
        ] : null;

        $this
            ->title(__p('core::phrase.username'))
            ->asPut()
            ->action(url_utility()->makeApiUrl('/account/setting'))
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $usernameRegex = Regex::getUsernameRegexSetting();
        $basic->addFields(
            Builder::text('user_name')
                ->required()
                ->label(__p('core::phrase.username'))
                ->placeholder(__p('user::phrase.choose_a_username'))
                ->setAttribute('contextualDescription', url_utility()->makeApiFullUrl(''))
                ->findReplace([
                    'find'    => MetaFoxConstant::SLUGIFY_FILTERS,
                    'replace' => MetaFoxConstant::SLUGIFY_FILTERS_REPLACE,
                ])
                ->yup(
                    Yup::string()
                        ->label(__p('core::phrase.user_name'))
                        ->required()
                        ->matches($usernameRegex, __p(Settings::get('regex.user_name_regex_error_message')))
                        ->minLength(
                            Settings::get('user.min_length_for_username', 5),
                            __p('validation.field_must_be_at_least_min_length_characters', [
                                'field'     => '${path}',
                                'minLength' => '${min}',
                            ])
                        )
                        ->maxLength(Settings::get('user.max_length_for_username'))
                ),
        );
    }
}
