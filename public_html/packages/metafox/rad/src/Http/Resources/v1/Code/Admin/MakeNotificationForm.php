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
 * Class MakeNotificationForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeNotificationForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Notification Class')
            ->action('admincp/rad/code/make/notification')
            ->asPost()
            ->setValue(
                [
                    'package'     => 'metafox/core',
                    '--name'      => false,
                    '--overwrite' => false,
                    '--dry'       => false,
                    '--ver'       => 'v1',
                    '--test'      => false,
                ]
            );
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::selectPackage('package')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable()
                ),
            Builder::text('--name')
                ->label('Notification Class')
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
