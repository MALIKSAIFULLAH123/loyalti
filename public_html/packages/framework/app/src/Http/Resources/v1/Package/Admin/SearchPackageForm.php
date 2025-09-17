<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use MetaFox\App\Support\Browse\Scopes\Package\TypeScope;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class BuiltinAdminSearchForm.
 *
 * Generic search form class for admincp.
 * @driverName ignore
 */
class SearchPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('app/package/browse/installed')
            ->acceptPageParams(['q', 'type', 'is_active'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'is_core'          => null,
                'is_active'        => null,
                'update_available' => null,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('type')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.type'))
                    ->options(TypeScope::getAllowOptions()),
                Builder::choice('update_available')
                    ->label(__p('app::phrase.updates_available'))
                    ->forAdminSearchForm()
                    ->options($this->getUpdateAvailableOptions()),
                Builder::choice('is_core')
                    ->label(__p('app::phrase.is_core'))
                    ->forAdminSearchForm()
                    ->options($this->getYesNoOptions()),
                Builder::choice('is_active')
                    ->label(__p('app::phrase.is_active'))
                    ->forAdminSearchForm()
                    ->options($this->getYesNoOptions()),
                Builder::submit()
                    ->forAdminSearchForm(),
                Builder::submit('is_checking_update')
                    ->setAttribute('randomValue', true)
                    ->variant('link')
                    ->sizeSmall()
                    ->sxFieldWrapper(['ml' => 1])
                    ->forAdminSearchForm()
                    ->label(__p('app::phrase.check_for_updates'))
                    ->color('success'),
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

    /**
     * @return array<int, mixed>
     */
    protected function getUpdateAvailableOptions(): array
    {
        return [
            ['label' => __p('app::phrase.update_now'), 'value' => 1],
            ['label' => __p('core::phrase.up_to_date'), 'value' => 0],
        ];
    }
}
