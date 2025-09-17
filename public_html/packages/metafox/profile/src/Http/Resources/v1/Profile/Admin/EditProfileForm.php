<?php

namespace MetaFox\Profile\Http\Resources\v1\Profile\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Profile\Models\Profile as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditProfileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditProfileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/profile/profile/' . $this->resource->id)
            ->asPut()
            ->setValue(new ProfileItem($this->resource));
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('profile_type')
                    ->required()
                    ->label(__p('core::phrase.name'))
                    ->yup(Yup::string()->maxLength(32)),
                Builder::text('title')
                    ->required()
                    ->label(__p('core::phrase.title'))
                    ->yup(Yup::string()->maxLength(200)),
            );

        $this->addDefaultFooter();
    }
}
