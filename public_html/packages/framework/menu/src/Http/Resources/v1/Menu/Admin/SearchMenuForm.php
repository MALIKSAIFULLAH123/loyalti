<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;

class SearchMenuForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('menu/menu/browse')
            ->submitAction(MetaFoxForm::FORM_ADMIN_SUBMIT_ACTION_SEARCH)
            ->acceptPageParams(['q', 'type', 'resolution', 'package_id'])
            ->setValue(['resolution' => 'web']);
    }

    protected function initialize(): void
    {
        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::choice('type')
                    ->label(__p('core::phrase.type'))
                    ->variant('outlined')
                    ->required()
                    ->options($this->getTypeOptions())
                    ->defaultValue(['label' => __p('menu::phrase.site_menu_label'), 'value' => 'site'])
                    ->freeSolo(false)
                    ->disableClearable()
                    ->forAdminSearchForm(),
                Builder::selectResolution('resolution')
                    ->disableClearable()
                    ->forAdminSearchForm(),
                Builder::selectPackage('package_id')
                    ->forAdminSearchForm(),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }

    public function getTypeOptions(): array
    {
        $types = [
            ['label' => __p('menu::phrase.site_menu_label'), 'value' => 'site'],
            ['label' => __p('menu::phrase.sidebar_menu_label'), 'value' => 'sidebar'],
            ['label' => __p('menu::phrase.profile_menu_label'), 'value' => 'profile'],
            ['label' => __p('menu::phrase.admin_top_menu_label'), 'value' => 'admin_top'],
        ];

        if ('local' === config('app.env')) {
            return array_merge($types, [['label' => __p('menu::phrase.context_menu_label'), 'value' => 'context']]);
        }

        return $types;
    }
}
