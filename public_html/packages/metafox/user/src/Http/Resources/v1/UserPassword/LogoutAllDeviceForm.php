<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserPassword;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\User\Http\Requests\v1\UserPassword\UpdateRequest;
use MetaFox\User\Support\Facades\User;

/**
 * @preload 0
 */
class LogoutAllDeviceForm extends AbstractForm
{
    protected string $token;

    public function boot(UpdateRequest $request): void
    {
        $params         = $request->validated();
        $this->token    = Arr::get($params, 'token');
        $this->resource = Arr::get($params, 'user');
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.password_changed'))
            ->description(__p('user::phrase.password_changed_description'))
            ->action(apiUrl('user.password.logoutAll'))
            ->secondAction('navigate')
            ->setValue([
                'user_id' => $this->resource->entityId(),
                'token'   => $this->token,
            ])
            ->asPatch();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::radioGroup('logout_others')
                ->options(User::getLogoutOptions()),
            Builder::hidden('user_id'),
            Builder::hidden('token'),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->sizeMedium()
                    ->label(__p('core::phrase.continue')),
            );
    }
}
