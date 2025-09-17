<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\UserPassword;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\User\Models\User;

/**
 * @property User $resource
 * @driverType form
 * @driverName user.password.verify_request
 * @resolution mobile
 * @preload    0
 */
class VerifyRequestMobileForm extends AbstractForm
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
        $this->title(__p('user::phrase.verification_code_title'))
            ->action(apiUrl('user.password.edit', ['resolution' => 'mobile']))
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
    }
}
