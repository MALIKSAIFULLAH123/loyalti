<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Core\Support\Facades\Currency;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Traits\MfaFieldTrait;
use MetaFox\Yup\Yup;

/**
 * @property Model $resource
 * @driverName user.account.info
 * @driverType form-mobile
 */
class AccountSettingMobileForm extends AbstractForm
{
    use MfaFieldTrait;

    /**
     * @throws AuthenticationException
     */
    public function boot(): void
    {
        /** @var Model $context */
        $context        = user();
        $this->resource = $context;

        policy_authorize(UserPolicy::class, 'updateSetting', $context, $this->resource);
    }

    protected function prepare(): void
    {
        if (!$this->resource instanceof Model) {
            return;
        }

        $profile = $this->resource->profile;

        $this
            ->title(__p('user::phrase.edit_account'))
            ->action('user/' . $this->resource->entityId())
            ->asPut()
            ->setValue([
                'profile' => [
                    'language_id' => $profile->language_id,
                    'currency_id' => $profile->currency_id,
                ],
                'full_name'    => $this->resource->display_name,
                'user_name'    => $this->resource->user_name,
                'email'        => $this->resource->email,
                'phone_number' => $this->resource->phone_number,
            ]);
    }

    public function initialize(): void
    {
        $basic = $this->addBasic();

        $this->handleFullNameField($basic);

        $usernameRegex = Regex::getRegexSetting('user_name');

        $basic->addFields(
            Builder::text('user_name')
                ->variant('standardInlined')
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
            $this->getEmailField(),
            $this->getPhoneField(),
            Builder::choice('profile.currency_id')
                ->required()
                ->label(__p('core::phrase.preferred_currency'))
                ->options(Currency::getActiveOptions())
        );
        $this->addCancelAccountSection();
    }

    protected function addCancelAccountSection(): void
    {
        if (!$this->canCancelAccount($this->resource)) {
            return;
        }

        $this->addSection(['name' => 'manage_account'])
            ->label(__p('user::phrase.manage_account'))
            ->addFields(
                Builder::clickable('cancel_account')
                    ->action('getCancelAccountForm')
                    ->label(__p('user::phrase.cancel_account'))
                    ->severity('danger')
                    ->params([
                        'id' => $this->resource->entityId(),
                    ]),
            );
    }

    protected function handleFullNameField(Section $basic): void
    {
        $fullNameYup = Yup::string()
            ->setError('typeError', __p('validation.full_name_is_a_required_field'));

        if (Settings::get('user.validate_full_name', true)) {
            $fullNameYup->maxLength(Settings::get('user.maximum_length_for_full_name'))
                ->minLength(3);
        }

        $basic->addField(
            Builder::text('full_name')
                ->variant('standardInlined')
                ->label(__p('user::phrase.display_name'))
                ->placeholder(__p('user::phrase.full_name'))
                ->yup($fullNameYup),
        );
    }

    public function canCancelAccount(User $user): bool
    {
        if ($user->hasSuperAdminRole()) {
            return false;
        }

        return policy_check(UserPolicy::class, 'delete', $user, $user);
    }

    protected function getEmailField(): AbstractField
    {
        $emailField = Builder::text('email')
            ->variant('standardInlined')
            ->required()
            ->label(__p('core::phrase.email_address'))
            ->placeholder(__p('core::phrase.email_address'))
            ->yup(
                Yup::string()
                    ->email(__p('validation.invalid_email_address'))
                    ->required()
            );

        $this->applyMfaRequiredEmailField($emailField);

        return $emailField;
    }

    protected function getPhoneField(): AbstractField
    {
        $phoneField = Builder::phoneNumber('phone_number');

        $this->applyMfaRequiredPhoneField($phoneField);

        return $phoneField;
    }
}
