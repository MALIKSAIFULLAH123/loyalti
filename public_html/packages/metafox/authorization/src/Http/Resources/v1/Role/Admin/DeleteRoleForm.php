<?php

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

use MetaFox\Authorization\Models\Role as Model;
use MetaFox\Authorization\Policies\RolePolicy;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Authorization\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\UserRole;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DeleteRoleForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteRoleForm extends AbstractForm
{
    protected bool $isEmptyRole;

    protected function prepare(): void
    {
        $this->title(__p('user::admin.delete_role'))
            ->action('/admincp/authorization/role/delete-role')
            ->asPost()
            ->setValue([
                'deleted_id' => $this->resource->entityId(),
            ]);

        if ($this->isEmptyRole) {
            $this->assignValue('delete_option', Support::DELETE_OPTION_PERMANENTLY);
        }
    }

    protected function initialize(): void
    {
        $this->handleConfirm();

        $this->addFooter()
            ->addFields(
                Builder::cancelButton(),
                Builder::submit()
                    ->label(__p('core::phrase.delete')),
            );
    }

    protected function handleConfirm(): Section
    {
        $basic = $this->addBasic();

        if ($this->isEmptyRole) {
            return $basic->addFields(Builder::typography('delete_confirm')
                ->tagName('strong')
                ->plainText(__p('user::admin.delete_role_question', ['name' => $this->resource->name])));
        }

        $basic->addFields(
            Builder::hidden('deleted_id'),
            Builder::typography('delete_question')
                ->tagName('strong')
                ->plainText(__p('user::admin.delete_role_question', ['name' => $this->resource->toTitle()])),
            Builder::description(__p('user::admin.first_description'))
                ->label(__p('user::admin.delete_role_notice')),
            Builder::radioGroup('delete_option')
                ->label(__p('authorization::phrase.you_have_two_options_when_deleting_a_role'))
                ->required()
                ->options($this->getDeleteOptions())
                ->yup(
                    Yup::string()
                        ->required(__p('authorization::phrase.delete_option_is_a_required_field'))
                        ->setError('typeError', __p('authorization::phrase.delete_option_is_a_required_field'))
                ),
            Builder::description(__p('user::admin.second_description'))
                ->label(__p('user::admin.delete_role_agreement', ['title' => $this->resource->toTitle()]))
                ->showWhen([
                    'eq',
                    'delete_option',
                    Support::DELETE_OPTION_MIGRATION,
                ]),
            Builder::choice('alternative_id')
                ->options($this->getOptions())
                ->requiredWhen([
                    'eq',
                    'delete_option',
                    Support::DELETE_OPTION_MIGRATION,
                ])
                ->showWhen([
                    'eq',
                    'delete_option',
                    Support::DELETE_OPTION_MIGRATION,
                ])
                ->label(__p('user::admin.alternative_role'))
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('delete_option')
                                ->is(Support::DELETE_OPTION_MIGRATION)
                                ->then(
                                    Yup::number()
                                        ->required(__p('user::admin.alternative_role_is_a_required_field'))
                                        ->setError('typeError', __p('user::admin.alternative_role_is_a_required_field'))
                                )
                        )
                ),
        );

        return $basic;
    }

    protected function getOptions(): array
    {
        $options = resolve(RoleRepositoryInterface::class)->getRoleOptions();

        $role = $this->resource;

        $disallowedRoles = [UserRole::SUPER_ADMIN_USER_ID, UserRole::PAGE_USER_ID];

        $options = array_filter($options, function ($option) use ($role, $disallowedRoles) {
            if (in_array($option['value'], $disallowedRoles)) {
                return false;
            }

            return $option['value'] != $role->entityId();
        });

        $options = array_values($options);

        return $options;
    }

    public function boot(int $id)
    {
        $role = resolve(RoleRepositoryInterface::class)->find($id);
        $role->loadCount('users');
        $context = user();

        policy_authorize(RolePolicy::class, 'delete', $context, $role);

        $this->resource    = $role;
        $this->isEmptyRole = !$this->resource->users_count
            && !$this->resource->subRoles()->exists();
    }

    protected function getDeleteOptions(): array
    {
        return resolve(RoleRepositoryInterface::class)->getDeleteOptions();
    }
}
