<?php

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class MakeInspectForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeInspectForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Inspect')
            ->action('admincp/rad/code/make/inspect')
            ->asPost()
            ->setValue([
                '--dry'     => false,
                '--package' => ['metafox/core'],
                '--inspect' => [],
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::selectPackage('--package.0')
                ->required(),
            Builder::checkboxGroup('--inspect')
                ->options([
                    ['value' => 'drivers', 'label' => 'Run inspect drivers to database'],
                    ['value' => 'phrases', 'label' => 'Run inspect phrases to database'],
                ]),
            Builder::checkbox('--dry')
                ->label('Dry run - does not write to files?')
                ->asBoolean()
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label('Generate Code'),
                Builder::cancelButton(),
            );
    }
}
