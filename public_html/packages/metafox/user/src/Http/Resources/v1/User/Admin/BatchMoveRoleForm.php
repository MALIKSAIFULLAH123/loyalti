<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User as Model;
use MetaFox\Yup\Yup;

/**
 * Class BatchMoveRoleForm.
 * @property Model $resource
 * @driverType form
 * @driverName user.batch_move_role
 */
class BatchMoveRoleForm extends AbstractForm
{
    private array $userIds;

    public function boot(Request $request): void
    {
        $this->userIds = json_decode($request->get('user_ids', []));
    }

    protected function prepare(): void
    {
        $this->action('admincp/user/batch-move-role')
            ->asPatch()
            ->setValue([
                'user_ids' => $this->userIds,
            ]);
    }

    public function initialize(): void
    {
        $this->title(__p('user::phrase.move_to_role'));

        $userGroup = $this->addSection([
            'name'  => 'role_id',
            'label' => __p('core::phrase.role'),
        ]);

        $roleOptions = array_filter(resolve(RoleRepositoryInterface::class)->getRoleOptions(), function ($role) {
            return Arr::get($role, 'value') != UserRole::SUPER_ADMIN_USER;
        });

        $userGroup->addFields(
            Builder::choice('role_id')
                ->required()
                ->multiple(false)
                ->disableClearable()
                ->label(__p('core::phrase.role'))
                ->options(array_values($roleOptions))
                ->yup(
                    Yup::number()
                        ->positive()
                        ->required()
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->confirmation(['message' => __p('user::phrase.skip_users_equal_or_higher')])
                    ->label(__p('core::phrase.submit')),
            );
    }
}
