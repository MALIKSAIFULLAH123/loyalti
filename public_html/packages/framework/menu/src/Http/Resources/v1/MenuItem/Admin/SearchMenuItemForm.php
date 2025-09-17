<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Menu\Http\Resources\v1\MenuItem\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;

class SearchMenuItemForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->acceptPageParams(['q', 'is_active', 'package_id'])
            ->submitAction(MetaFoxForm::FORM_ADMIN_SUBMIT_ACTION_SEARCH);
    }

    protected function initialize(): void
    {
        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::selectPackage('package_id')
                    ->forAdminSearchForm(),
                Builder::choice('is_active')
                    ->label(__p('core::phrase.status'))
                    ->options($this->getStatusOptions())
                    ->forAdminSearchForm(),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getStatusOptions(): array
    {
        return [
            ['label' => __p('core::phrase.is_active'), 'value' => 1],
            ['label' => __p('core::phrase.inactive'), 'value' => 0],
            ['label' => __p('core::phrase.all'), 'value' => null],
        ];
    }
}
