<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Illuminate\Support\Arr;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class BanUserForm.
 * @property Model $resource
 * @driverType form
 * @driverName user.ban
 */
class BanUserForm extends AbstractForm
{
    public function boot(int $id, UserRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $user = $this->resource;

        $this->action('admincp/user/ban')
            ->asPost()
            ->setValue([
                'user_id' => $user->entityId(),
            ]);
    }

    public function initialize(): void
    {
        $user = $this->resource;

        $this->title(__p('user::phrase.ban_user'));

        $this->addBasic()->addFields(
            Builder::typography('info_typo')
                ->variant('h5')
                ->plainText(__p(
                    'user::phrase.you_are_about_to_ban_the_user',
                    ['username' => $user->user_name, 'link' => $user->toUrl()]
                )),
            Builder::typography('reason_typo')
                ->variant('h5')
                ->plainText(__p('user::phrase.reason')),
            Builder::textArea('reason')
                ->returnKeyType('default')
                ->label(__p('user::phrase.reason')),
            Builder::typography('day_typo')
                ->variant('h5')
                ->plainText(__p('user::phrase.ban_for_how_many_days')),
            Builder::text('day')
                ->required()
                ->asNumber()
                ->label(__p('user::phrase.ban_for_how_many_days'))
                ->description(__p('user::phrase.0_means_indefinite'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                        ->unint()
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                ),
            Builder::typography('return_user_group_typo')
                ->variant('h5')
                ->plainText(__p('core::phrase.role')),
            Builder::choice('return_user_group')
                ->required()
                ->multiple(false)
                ->disableClearable()
                ->label(__p('core::phrase.role'))
                ->description(__p('user::phrase.role_to_move_the_user_when_the_ban_expires'))
                ->options($this->getRoleOptions())
                ->yup(
                    Yup::number()
                        ->positive()
                        ->required()
                ),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('user::phrase.ban_user')),
                Builder::cancelButton()
                    ->noConfirmation()
            );
    }

    protected function getRoleOptions(): array
    {
        $roleOptions = array_filter(resolve(RoleRepositoryInterface::class)->getRoleOptions(), function ($role) {
            return !in_array(Arr::get($role, 'value'), [UserRole::SUPER_ADMIN_USER, UserRole::BANNED_USER]);
        });

        return array_values($roleOptions);
    }
}
