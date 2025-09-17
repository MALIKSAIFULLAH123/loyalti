<?php

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class MakePolicyForm.
 * @ignore
 * @codeCoverageIgnore
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makePolicyForm()
 */
class MakePolicyForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Policy Class')
            ->action('admincp/rad/code/make/policy')
            ->asPost()
            ->setValue(
                [
                    '--name'      => '',
                    '--overwrite' => false,
                    '--entity'    => '',
                    '--dry'       => false,
                    '--test'      => false,
                    'package'     => 'metafox/core',
                ]
            );
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
                        ->nullable(false)
                ),
            Builder::text('--name')
                ->label('Model Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('--entity')
                ->label('Entity Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->matches('^([a-z_]+)$')
                ),
            Builder::checkbox('--overwrite')
                ->label('Overwrite existing files?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--test')
                ->label('Generate an accompanying PHPUnit test case')
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
