<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserPassword;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\MultiStepFormTrait;
use MetaFox\User\Http\Requests\v1\UserPassword\RequestMethodFormRequest;
use MetaFox\User\Contracts\CanResetPassword;

/**
 * @driverType form
 * @driverName user.password.request_method
 * @resolution web
 * @preload    0
 */
class RequestMethodForm extends AbstractForm
{
    use MultiStepFormTrait;

    public function boot(RequestMethodFormRequest $request): void
    {
        $params         = $request->validated();
        $this->resource = Arr::get($params, 'user');
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.reset_your_password_title'))
            ->action(apiUrl('user.password.request.verify', ['resolution' => 'web']))
            ->setValue([
                'user_id' => $this->resource->entityId(),
            ])
            ->asPost();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $methods = [];

        if ($this->resource instanceof CanResetPassword) {
            $methods = $this->resource->getResetMethods();
        }

        $basic->addFields(
            Builder::radioGroup('request_method')
                ->label(__p('user::phrase.where_to_send_code'))
                ->description(__p('user::phrase.where_to_send_code_desc'))
                ->required()
                ->options($methods),
            Builder::hidden('user_id'),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('user::phrase.send_verification_code')),
            );
    }
}
