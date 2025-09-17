<?php

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class MakeListenerForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeListenerForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Listener Class')
            ->action('admincp/rad/code/make/listener')
            ->asPost()
            ->setValue([
                'package'     => 'metafox/core',
                '--name'      => false,
                '--overwrite' => false,
                '--dry'       => false,
                '--ver'       => 'v1',
                '--test'      => false,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::selectPackage('package')
                ->label(__p('core::phrase.package_name'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable()
                ),
            Builder::text('--name')
                ->label('Listener Class')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->matches('^([A-Z][\w]+)$', 'Invalid format')
                ),
            Builder::hidden('--ver'),
            Builder::checkbox('--dry')
                ->label('Dry run?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--overwrite')
                ->label('Overwrite existing files?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--test')
                ->label('Generate an accompanying PHPUnit test case')
                ->checkedValue(true)
                ->uncheckedValue(false),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label('Generate Code'),
                Builder::cancelButton(),
            );
    }
}
