<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * Class MakeDataGridForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeDataGridForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Make Data Grid')
            ->action('admincp/rad/code/make/data_grid')
            ->asPost()
            ->setValue([
                'package'     => 'metafox/core',
                '--overwrite' => false,
                '--ver'       => 'v1',
                '--dry'       => false,
                '--name'      => '',
                '--action'    => '',
                '--admin'     => true,
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
                ->label('Model Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('--action')
                ->label('Action Name (optional)')
                ->required(false),
            Builder::hidden('--ver'),
            Builder::checkbox('--admin')
                ->label('Admin?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--dry')
                ->label('Dry run - does not write to files?')
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
