<?php

namespace MetaFox\ChatPlus\Http\Resources\v1\User\Admin;

use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\Eloquent\UserRepository;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UserSettingForm.
 */
class UserSettingForm extends Form
{
    protected function prepare(): void
    {
        $this->config([
            'title'  => __p('chatplus::phrase.user_setting'),
            'action' => url_utility()->makeApiUrl('/chatplus/export-users'),
            'method' => MetaFoxForm::METHOD_GET,
            'value'  => [],
        ]);
    }

    protected function initialize(): void
    {
        /** @var UserRepository $server */
        $count = resolve(UserRepositoryInterface::class)->count();
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::typography()
                ->name('title')
                ->variant('h5')
                ->margin('dense')
                ->plainText(__p('chatplus::phrase.total_user_notice', ['count' => $count]))
        );
        $basic->addFields(
            Builder::typography()
                ->name('sub_title')
                ->variant('h6')
                ->color('text.secondary')
                ->plainText(__p('chatplus::phrase.sync_user_notice'))
        );
        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('chatplus::phrase.sync_users'))
                    ->color('success')
                    ->variant('contained')
                    ->name('sync_users ')
                    ->sizeNormal()
            );
    }
}
