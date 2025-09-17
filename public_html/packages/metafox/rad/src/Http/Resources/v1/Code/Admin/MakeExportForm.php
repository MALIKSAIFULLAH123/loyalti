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
 * Class MakeExportForm.
 * @ignore
 * @codeCoverageIgnore
 * @driverType form
 * @driverName rad.code.make_export
 */
class MakeExportForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Exports')
            ->action('admincp/rad/code/make/inspect')
            ->asPost()
            ->setValue([
                '--dry'     => false,
                '--package' => ['metafox/core'],
                '--publish' => ['menus', 'phrases', 'drivers', 'pages'],
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::selectPackage('--package.0')
                ->required(),
            Builder::checkboxGroup('--publish')
                ->options([
                    ['value' => 'menus', 'label' => 'Export menus & items'],
                    ['value' => 'drivers', 'label' => 'Export drivers'],
                    ['value' => 'phrases', 'label' => 'Export phrases'],
                    ['value' => 'pages', 'label' => 'Export SEO meta'],
                ]),
            Builder::checkbox('--dry')
                ->label('Dry run - does not write to files?')
                ->asBoolean()
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label('Export'),
                Builder::cancelButton(),
            );
    }
}
