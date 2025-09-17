<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserPassword;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Contracts\CanResetPassword;
use MetaFox\User\Models\User;

/**
 * @property User $resource
 * @driverType form
 * @driverName user.password.request_method
 * @resolution mobile
 * @preload    0
 */
class RequestMethodMobileForm extends AbstractForm
{
    public function boot(): void
    {
        $this->setMultiStepFormMeta([
            'continueAction' => [
                'type' => 'formSchema',
            ],
        ]);
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.reset_your_password_title'))
            ->action(apiUrl('user.password.request.verify', ['resolution' => 'mobile']))
            ->setValue([
                'user_id'   => $this->resource->entityId(),
                'user_type' => $this->resource->entityType(),
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
    }
}
