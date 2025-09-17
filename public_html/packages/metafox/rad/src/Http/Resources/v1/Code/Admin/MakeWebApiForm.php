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
 * Class MakeWebApiForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeWebApiForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Web Apis')
            ->action('admincp/rad/code/make/web_api')
            ->asPost()
            ->setValue([
                '--overwrite' => false,
                '--ver'       => 'v1',
                '--admin'     => false,
                '--dry'       => false,
                '--name'      => false,
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
            Builder::hidden('--admin'),
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
