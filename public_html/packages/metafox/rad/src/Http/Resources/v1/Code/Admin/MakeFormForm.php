<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Html\Hidden;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Yup\Yup;

/**
 * Class MakeFormsForm.
 * @ignore
 * @codeCoverageIgnore
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeForm()
 */
class MakeFormForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Forms')
            ->action('admincp/rad/code/make/form')
            ->asPost()
            ->setValue([
                'package'     => 'metafox/core',
                '--form'      => false,
                '--action'    => '',
                '--overwrite' => false,
                '--dry'       => false,
                '--ver'       => 'v1',
                '--admin'     => false,
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
            new Hidden(['name' => '--ver']),
            Builder::text('--action')
                ->required()
                ->label('Action Name')
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::checkbox('--admin')
                ->label('Is Admin Forms')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--request')
                ->label('Create associate Form Request class?')
                ->checkedValue(true)
                ->uncheckedValue(false),
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
                Builder::cancelButton(),
                Builder::submit()
                    ->label('Generate Code'),
            );
    }
}
