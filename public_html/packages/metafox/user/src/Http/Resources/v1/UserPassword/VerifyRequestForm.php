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
use MetaFox\User\Http\Requests\v1\UserPassword\VerifyRequest;
use MetaFox\User\Models\User;

/**
 * @property User $resource
 * @driverType form
 * @driverName user.password.verify_request
 * @resolution web
 * @preload    0
 */
class VerifyRequestForm extends AbstractForm
{
    use MultiStepFormTrait;

    public function boot(VerifyRequest $request): void
    {
        $data           = $request->validated();
        $this->resource = Arr::get($data, 'user');
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.verification_code_title'))
            ->action(apiUrl('user.password.edit', ['resolution' => 'web']))
            ->setValue([
                'user_id' => $this->resource->entityId(),
            ])
            ->asPost();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::numberCode('token')
                ->label(__p('user::phrase.enter_your_verification_code')),
            Builder::hidden('user_id'),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('user::phrase.continue')),
            );
    }
}
