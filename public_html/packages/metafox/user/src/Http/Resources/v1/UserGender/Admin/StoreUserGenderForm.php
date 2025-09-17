<?php

namespace MetaFox\User\Http\Resources\v1\UserGender\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\UserGender as Model;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreUserGenderForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName user.user_gender.store
 * @driverType form
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StoreUserGenderForm extends AbstractForm
{
    public function boot(UserGenderRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = new Model();
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.add_new_gender'))
            ->action('/admincp/user/user-gender')
            ->asPost()
            ->setValue([
                'is_custom' => 1,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::translatableText('phrase')
                ->required()
                ->label(__p('localize::phrase.language'))
                ->buildFields(),
            Builder::hidden('is_custom'),
        );

        $this->addDefaultFooter();
    }
}
