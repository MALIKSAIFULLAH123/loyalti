<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * MakePackageForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class MakePackageForm.
 */
class MakePackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('core::phrase.create_module'))
            ->action('/admincp/rad/code/make/new_app')
            ->asPost()
            ->setValue([
                '--dry'      => false,
                '--vendor'   => 'MetaFox',
                '--name'     => '',
                '--author'   => 'Dev',
                '--homepage' => 'https://metafoxapp.com',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('package')
                ->required()
                ->label(__p('core::phrase.package_name'))
                ->maxLength(64)
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false)
                        ->matches('^([a-z][\w]+)/([a-z][\w]+)$', 'Invalid package format')
                ),
            Builder::text('--vendor')
                ->required()
                ->label(__p('core::phrase.vendor_name'))
                ->maxLength(64)
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false)
                        ->matches('^([A-Z][\w]+)$', 'Invalid format, etc: Note, ActivityPoint')
                ),
            Builder::text('--name')
                ->required()
                ->label(__p('core::phrase.name'))
                ->maxLength(64)
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable(false)
                        ->matches('^([A-Z][\w]+)$', 'Invalid format, etc: Note, ActivityPoint, StaticPage')
                ),
            Builder::text('--author')
                ->required()
                ->label(__p('app::phrase.author_name'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('--homepage')
                ->required()
                ->label(__p('app::phrase.author_homepage'))
                ->yup(
                    Yup::string()
                        ->required()
                        ->url()
                )
        );

        $this->addDefaultFooter(false);
    }
}
