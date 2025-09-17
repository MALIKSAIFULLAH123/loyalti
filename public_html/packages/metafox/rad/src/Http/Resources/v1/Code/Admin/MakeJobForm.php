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
 * Class MakeJobCodeForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeJobForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Job Class')
            ->action('admincp/rad/code/make/job')
            ->asPost()
            ->setValue([
                '--name'      => '',
                '--overwrite' => false,
                '--admin'     => false,
                '--dry'       => false,
                '--sync'      => false,
                '--test'      => false,
                'package'     => 'metafox/core',
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
                ->label('Job Class Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->matches('^([A-Z][\w]+)$', 'Invalid format')
                ),
            Builder::checkbox('--sync')
                ->label('Indicates that job should be synchronous?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--overwrite')
                ->label('Overwrite existing files?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--test')
                ->label('Generate an accompanying PHPUnit test class.')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--dry')
                ->label('Dry run - does not write to files?')
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
