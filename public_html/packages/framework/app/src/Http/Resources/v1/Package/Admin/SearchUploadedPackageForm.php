<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use MetaFox\App\Support\Browse\Scopes\Package\TypeScope;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * Class BuiltinAdminSearchForm.
 *
 * Generic search form class for admincp.
 * @driverName ignore
 */
class SearchUploadedPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->setValue([
            'is_core' => null,
        ]);
    }

    protected function initialize(): void
    {
        $this->acceptPageParams(['q', 'type']);

        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('type')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.type'))
                    ->options(TypeScope::getAllowOptions()),
                Builder::choice('is_core')
                    ->label(__p('app::phrase.is_core'))
                    ->forAdminSearchForm()
                    ->options($this->getYesNoOptions()),
                Builder::submit()
                    ->forAdminSearchForm(),
            );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getYesNoOptions(): array
    {
        return [
            ['label' => __p('core::phrase.yes'), 'value' => 1],
            ['label' => __p('core::phrase.no'), 'value' => 0],
        ];
    }
}
