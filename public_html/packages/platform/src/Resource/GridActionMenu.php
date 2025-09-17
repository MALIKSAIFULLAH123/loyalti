<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use MetaFox\Form\Constants as MetaFoxForm;

class GridActionMenu extends MenuConfig
{
    /**
     * @param string|null $label
     */
    public function withCreate(?string $label = null): MenuItem
    {
        return $this->addItem('addItem')
            ->icon('ico-plus')
            ->value(MetaFoxForm::ACTION_ROW_ADD)
            ->label($label ?? __p('core::phrase.create'))
            ->disabled(false)
            ->params(['action' => 'addItem']);
    }
}
