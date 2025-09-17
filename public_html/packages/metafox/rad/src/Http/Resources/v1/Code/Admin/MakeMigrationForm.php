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
 * Class MakeMigrationForm.
 * @ignore
 * @codeCoverageIgnore
 * @link \MetaFox\Rad\Http\Controllers\Api\v1\CodeAdminController::makeMigrationForm()
 * @link \MetaFox\Rad\Http\Requests\v1\Code\Admin\MakeMigrationRequest
 */
class MakeMigrationForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Migration')
            ->action('admincp/rad/code/make/migration')
            ->asPost()
            ->setValue(
                [
                    'package'     => 'metafox/core',
                    '--dry'       => false,
                    '--overwrite' => true,
                    '--name'      => '',
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
                        ->nullable()
                ),
            Builder::text('--name')
                ->label('Schema Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
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
