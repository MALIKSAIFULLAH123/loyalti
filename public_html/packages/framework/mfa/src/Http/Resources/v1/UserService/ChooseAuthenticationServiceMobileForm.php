<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Mfa\Models\UserAuthToken as Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Yup\Yup;

/**
 * @property Model $resource
 * @driverType form
 * @driverName mfa.user_service.choose_authentication_service_form
 * @resolution mobile
 * @preload    0
 */
class ChooseAuthenticationServiceMobileForm extends ChooseAuthenticationServiceForm
{
    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type' => 'formSchema',
            ],
        ]);
    }

    protected function prepare(): void
    {
        $user = $this->resource?->user;
        if (empty($this->resource) || !$user instanceof User) {
            return;
        }

        $this->title(__p('mfa::phrase.choose_an_authentication_service'))
            ->action(apiUrl('mfa.user.auth.formPost', ['resolution' => 'mobile']))
            ->asPost()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::radioGroup('service')
                ->label(__p('mfa::phrase.please_choose_one_of_the_methods_below'))
                ->required()
                ->options($this->getServiceOptions())
                ->yup(
                    Yup::string()
                        ->required(__p('mfa::phrase.service_option_is_a_required_field'))
                        ->setError('typeError', __p('mfa::phrase.service_option_is_a_required_field'))
                ),
        );
    }
}
