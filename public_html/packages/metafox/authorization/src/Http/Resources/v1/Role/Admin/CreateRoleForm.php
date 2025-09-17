<?php

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Authorization\Models\Role as Model;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\UserRole;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateRoleForm.
 * @property ?Model $resource
 *
 * @driverType form
 * @driverName user.user_role.store
 * @
 */
class CreateRoleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('authorization::phrase.new_role'))
            ->action(apiUrl('admin.authorization.role.store'))
            ->asPost()
            ->setValue([
                'name'           => MetaFoxConstant::EMPTY_STRING,
                'inherited_role' => UserRole::NORMAL_USER_ID,
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $context = user();
        $basic   = $this->addBasic();
        $basic->addFields(
            Builder::text('name')
                ->required()
                ->returnKeyType('next')
                ->label(__p('core::phrase.title'))
                ->description(__p('user::phrase.role_title_description'))
                ->yup(
                    Yup::string()->required()
                ),
        );

        if (!$this->isEdit()) {
            $basic->addFields(
                Builder::choice('inherited_role')
                    ->label(__p('user::phrase.inherited_role_field_label'))
                    ->description(__p('user::phrase.inherited_role_field_description'))
                    ->options($this->getRoleOptions($context))
                    ->required()
                    ->disableClearable()
                    ->yup(
                        Yup::number()->required()
                    )
            );
        }

        $this->addDefaultFooter();
    }

    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId() > 0;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getRoleOptions(User $context): array
    {
        return resolve(RoleRepositoryInterface::class)->getRoleOptionsWithContextRole($context);
    }
}
