<?php

namespace MetaFox\Profile\Http\Resources\v1\Profile\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Profile\Models\Profile as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateProfileForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateProfileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->action('/admincp/profile/profile')
            ->asPost()
            ->setValue([
                'user_type' => 'user',
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('user_type')
                    ->required()
                    ->label(__p('core::phrase.type'))
                    ->yup(Yup::string()->maxLength(32)),
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
