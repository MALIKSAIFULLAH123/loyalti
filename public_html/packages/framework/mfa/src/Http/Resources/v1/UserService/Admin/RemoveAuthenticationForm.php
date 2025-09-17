<?php

namespace MetaFox\Mfa\Http\Resources\v1\UserService\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class RemoveAuthenticationForm.
 * @property Model $resource
 * @driverType form
 * @driverName mfa.user_service.deactivate_authenticator_form
 */
class RemoveAuthenticationForm extends AbstractForm
{
    public function boot(int $id, UserRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->action('admincp/mfa/authentication')
            ->asDelete()
            ->setValue([
                'user_id' => $this->resource->entityId(),
            ]);
    }

    public function initialize(): void
    {
        $this->title(__p('mfa::phrase.choose_an_authentication_service'));

        $this->addBasic()->addFields(
            Builder::checkboxGroup('services')
                ->label(__p('mfa::phrase.please_select_the_services_you_want_to_remove'))
                ->required()
                ->options($this->getServiceOptions())
                ->multiple()
                ->yup(
                    Yup::array()
                        ->required(__p('mfa::phrase.service_option_is_a_required_field'))
                        ->setError('typeError', __p('mfa::phrase.service_option_is_a_required_field'))
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::web.remove')),
                Builder::cancelButton()
                    ->noConfirmation()
            );
    }

    protected function getServiceOptions(): array
    {
        return resolve(UserServiceRepositoryInterface::class)->getActivatedServicesForForm($this->resource);
    }
}
