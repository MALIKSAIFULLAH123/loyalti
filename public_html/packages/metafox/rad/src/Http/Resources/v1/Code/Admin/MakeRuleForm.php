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
 * Class MakeRuleForm.
 * @ignore
 * @codeCoverageIgnore
 * @link \MetaFox\Core\Http\Controllers\Api\v1\CodeAdminController::makeRuleForm()
 */
class MakeRuleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Rule Class')
            ->action('admincp/rad/code/make/rule')
            ->asPost()
            ->setValue([
                'package'     => 'metafox/core',
                '--name'      => false,
                '--overwrite' => false,
                '--dry'       => false,
                '--ver'       => 'v1',
                '--implicit'  => false,
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
                        ->nullable(false)
                ),
            Builder::text('--name')
                ->label('Rule Class Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->matches('^([A-Z][\w]+)$', 'Invalid format')
                ),
            Builder::checkbox('--dry')
                ->label('Dry run?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--implicit')
                ->label('Generate an implicit rule.')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--overwrite')
                ->label('Overwrite existing files?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--test')
                ->label('Generate sample test class?')
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
