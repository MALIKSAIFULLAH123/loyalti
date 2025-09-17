<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Mfa\Models\UserAuthToken as Model;
use MetaFox\Mfa\Models\UserVerifyCode;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Platform\Contracts\User;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SmsAuthForm.
 *
 * @property ?Model $resource
 */
abstract class AbstractAuthForm extends AbstractForm
{
    public ?string $previousProcessChildId = 'mfa_get_choose_authentication_form';
    public ?string $previousProcessId      = null;

    public function getPreviousProcessChildId(): ?string
    {
        return $this->previousProcessChildId;
    }

    public function setPreviousProcessChildId(?string $previousProcessChildId): void
    {
        $this->previousProcessChildId = $previousProcessChildId;
    }

    public function getPreviousProcessId(): ?string
    {
        return $this->previousProcessId;
    }

    public function setPreviousProcessId(?string $previousProcessId): void
    {
        $this->previousProcessId = $previousProcessId;
    }

    abstract protected function getService(): string;

    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type'    => 'multiStepForm/next',
                'payload' => [
                    'formName'               => 'mfa_authentication_form',
                    'processChildId'         => $this->getPreviousProcessId(),
                    'previousProcessChildId' => $this->getPreviousProcessChildId(),
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

        $value = array_merge($this->getValue(), [
            'action'   => UserVerifyCode::AUTH_ACTION,
            'service'  => $this->getService(),
            'password' => $this->resource->value,
        ]);

        $this->description(__p('mfa::phrase.authenticator_login_description'))
            ->action(apiUrl('mfa.user.auth.auth'))
            ->secondAction('@loginAuthentication')
            ->asPost()
            ->setValue($value);
    }

    protected function buildFooter(): void
    {
        $footer = $this->addFooter();
        $footer->addFields(
            Builder::submit()
                ->label(__p('mfa::phrase.verify')),
            $this->getPreviousProcessChildId() == null
                ? Builder::cancelButton()
                : Builder::customButton()
                ->label(__p('core::phrase.back'))
                ->customAction([
                    'type'    => 'multiStepForm/previous',
                    'payload' => [
                        'formName'               => 'mfa_authentication_form',
                        'previousProcessChildId' => $this->getPreviousProcessChildId(),
                    ],
                ]),
        );
    }
}
