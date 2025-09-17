<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use MetaFox\Form\Constants as MetaFoxForm;

class BatchActionMenu extends MenuConfig
{
    public function withDelete(): MenuItem
    {
        return $this->addItem('deleteItem')
            ->icon('ico-trash')
            ->value(MetaFoxForm::ACTION_BATCH_DELETE)
            ->label(__p('core::phrase.delete'))
            ->style('danger')
            ->params(['action' => 'deleteItems']);
    }

    public function withCreate(string $label): MenuItem
    {
        return $this->addItem('addItem')
            ->icon('ico-plus')
            ->value(MetaFoxForm::ACTION_ROW_ADD)
            ->label($label)
            ->disabled(false)
            ->params(['action' => 'addItem']);
    }
}
