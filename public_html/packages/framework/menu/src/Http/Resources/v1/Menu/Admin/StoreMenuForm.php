<?php

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * StoreMenuForm
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreMenuForm.
 * @ignore
 */
class StoreMenuForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title(__p('menu::phrase.add_new_menu'))
            ->action(apiUrl('admin.menu.menu.store'))
            ->asPost()
            ->setValue([
                'is_active'  => 1,
                'resolution' => 'web',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->maxWidth('400px');

        $basic->addFields(
            Builder::selectPackageAlias('module_id')
                ->required()
                ->label(__p('core::phrase.package_name'))
                ->yup(
                    Yup::string()->required()
                ),
            Builder::text('name')
                ->required()
                ->label('Name')
                ->maxLength(128)
                ->yup(
                    Yup::string()->required()
                ),
            Builder::dropdown('resolution')
                ->label(__p('menu::phrase.resolution'))
                ->required()
                ->options($this->getResolutionOptions())
                ->yup(
                    Yup::string()->required()
                ),
            Builder::text('resource_name')
                ->required(false)
                ->label(__p('menu::phrase.resource_name'))
                ->maxLength(255)
                ->yup(Yup::string()->optional()->nullable()),
        );

        $this->addDefaultFooter();
    }

    protected function getResolutionOptions(): array
    {
        return [
            ['value' => 'web', 'label' => 'web'],
            ['value' => 'admin', 'label' => 'admin'],
            ['value' => 'mobile', 'label' => 'mobile'],
        ];
    }
}
