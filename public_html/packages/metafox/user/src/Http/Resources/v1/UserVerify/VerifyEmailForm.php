<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserVerify;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserVerify;
use MetaFox\Yup\Yup;

/**
 * Class VerifyEmailForm.
 * @preload    1
 */
class VerifyEmailForm extends AbstractForm
{
    protected string $verifiable;
    public function __construct(string $verifiable = null, User $resource = null)
    {
        parent::__construct($resource);

        $this->verifiable = $verifiable;
    }

    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'user_change_email_form',
                    'processChildId'         => 'user_get_verify_email_form',
                    'previousProcessChildId' => 'user_get_update_email_form',
                ],
            ],
        ]);
    }

    protected function prepare(): void
    {
        $this->title(__p('user::web.verify_your_email'))
            ->description(__p('user::web.need_verify_your_email_continue'))
            ->asPost()
            ->action(apiUrl('user.verify.verify'))
            ->setValue([
                'user_id' => $this->resource->id,
                'email'   => $this->verifiable,
                'action'  => UserVerify::ACTION_EMAIL,
            ]);
    }

    protected function initialize(): void
    {
        if (empty($this->resource) || empty($this->verifiable)) {
            return;
        }

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::typography('resend_email_description')
                ->plainText(__p('user::web.resend_email_description')),
            Builder::numberCode('verification_code')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(6, __p('user::phrase.verification_code_must_be_a_number_with_six_digits'))
                        ->matchesAsNumeric(__p('user::phrase.verification_code_must_be_a_number_with_six_digits'), false)
                        ->setError('required', __p('user::phrase.verification_code_is_a_required_field'))
                ),
            Builder::customButton('resend')
                ->label(__p('user::web.did_not_receive_resend_email'))
                ->variant('link')
                ->sizeNormal()
                ->customAction([
                    'type'    => 'user/verify/resend',
                    'payload' => [
                        'module_name'   => 'user',
                        'resource_name' => 'user_verify',
                        'action_name'   => 'resend',
                        'data'          => $this->getValue(),
                    ],
                ]),
        );

        $this->buildFooter();
    }

    protected function buildFooter(): void
    {
        $this->addFooter()->addFields(
            Builder::submit()
                ->label(__p('user::phrase.verify'))
        );
    }
}
