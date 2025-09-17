<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use MetaFox\Form\Builder;
use MetaFox\Mfa\Models\Service;
use MetaFox\Mfa\Models\UserAuthToken as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class AuthenticatorAuthForm.
 *
 * @property ?Model $resource
 */
class AuthenticatorAuthForm extends AbstractAuthForm
{
    protected function getService(): string
    {
        return Service::AUTHENTICATOR_SERVICE;
    }

    public ?string $previousProcessId = 'mfa_get_authenticator_authentication_form';

    protected function prepare(): void
    {
        parent::prepare();

        $this->title(__p('mfa::phrase.authenticator'));
    }

    protected function initialize(): void
    {
        if (empty($this->resource)) {
            return;
        }

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::numberCode('verification_code')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(6, __p('mfa::phrase.authenticator_code_must_be_a_number_with_six_digits'))
                        ->matchesAsNumeric(__p('mfa::phrase.authenticator_code_must_be_a_number_with_six_digits'), false)
                        ->setError('required', __p('mfa::phrase.authenticator_code_is_a_required_field'))
                ),
        );

        $this->buildFooter();
    }
}
