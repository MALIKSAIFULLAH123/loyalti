<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Mfa\Models\UserAuthToken as Model;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Contracts\User;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class ChooseAuthenticationServiceForm.
 *
 * @property ?Model $resource
 */
class ChooseAuthenticationServiceForm extends AbstractForm
{
    public function __construct($resource = null)
    {
        parent::__construct($resource);
    }

    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'mfa_authentication_form',
                    'processChildId'         => 'mfa_get_choose_authentication_form',
                    'previousProcessChildId' => null,
                ],
            ],
        ]);

        $this->setValue(Arr::only(request()->all(), Mfa::getAuthQueryParamsAttribute()));
    }

    protected function prepare(): void
    {
        $user = $this->resource?->user;
        if (empty($this->resource) || !$user instanceof User) {
            return;
        }

        $this->title(__p('mfa::phrase.choose_an_authentication_service'))
            ->action(apiUrl('mfa.user.auth.formPost'))
            ->asPost();
    }

    protected function initialize(): void
    {
        if (empty($this->resource)) {
            return;
        }

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

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::web.next')),
                Builder::cancelButton(),
            );
    }

    protected function getServiceOptions(): array
    {
        $user = $this->resource?->user;

        return resolve(UserServiceRepositoryInterface::class)->getActivatedServicesForForm($user, true);
    }

    protected function getServiceDefault(): ?string
    {
        return Arr::first(Arr::pluck($this->getServiceOptions(), 'value'));
    }

    public function setValue(mixed $value): static
    {
        return parent::setValue(array_merge($value, [
            'mfa_token' => $this->resource->value,
            'service'   => $this->getServiceDefault(),
        ]));
    }
}
