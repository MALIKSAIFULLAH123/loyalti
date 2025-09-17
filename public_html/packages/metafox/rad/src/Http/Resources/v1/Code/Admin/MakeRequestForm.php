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
 * Class MakeFormRequestForm.
 * @ignore
 * @codeCoverageIgnore
 * @link
 */
class MakeRequestForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Form Requests')
            ->action('admincp/rad/code/make/request')
            ->asPost()
            ->setValue([
                '--action'    => '',
                '--overwrite' => false,
                '--ver'       => 'v1',
                '--admin'     => false,
                '--dry'       => false,
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
                        ->nullable(false)
                ),
            Builder::text('--name')
                ->label('Model Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::hidden('--ver'),
            Builder::text('--action')
                ->required()
                ->label('Request')
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::checkbox('--admin')
                ->label('Is Admin Requests')
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
