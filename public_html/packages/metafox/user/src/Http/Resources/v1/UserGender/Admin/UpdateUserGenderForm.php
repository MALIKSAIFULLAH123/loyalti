<?php

namespace MetaFox\User\Http\Resources\v1\UserGender\Admin;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\UserGender as Model;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateUserGenderForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName user.user_gender.update
 * @driverType form
 */
class UpdateUserGenderForm extends StoreUserGenderForm
{
    public function boot(UserGenderRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.update_gender'))
            ->action('/admincp/user/user-gender/' . $this->resource->entityId())
            ->asPut()
            ->setValue([
                'phrase' => Language::getPhraseValues($this->resource->phrase),
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::translatableText('phrase')
                ->required()
                ->label(__p('user::phrase.gender'))
                ->buildFields(),
        );

        $this->addDefaultFooter();
    }
}
