<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class ItemActionMenu.
 *
 * Describe ItemActionMenu
 */
class ItemActionMenu extends MenuConfig
{
    /**
     * Add "editItem".
     *
     * @param string|null $label
     *
     * @return MenuItem
     */
    public function withEdit(?string $label = null): MenuItem
    {
        return $this->addItem('edit')
            ->icon('ico-pencil-o')
            ->value(MetaFoxForm::ACTION_ROW_EDIT)
            ->label($label ?? __p('core::phrase.edit'))
            ->params(['action' => 'edit']);
    }

    /**
     * Add "deleteItem".
     *
     * @param  string|null $label
     * @param  array|null  $confirmation
     * @param  array       $showWhen
     * @return MenuItem
     */
    public function withDelete(?string $label = null, ?array $confirmation = null, array $showWhen = []): MenuItem
    {
        return $this->addItem('delete')
            ->icon('ico-trash')
            ->value(MetaFoxForm::ACTION_ROW_DELETE)
            ->label($label ?? __p('core::phrase.delete'))
            ->action('destroy')
            ->confirm($confirmation ?: true)
            ->showWhen($showWhen);
    }

    /**
     * Add "getDeleteForm".
     *
     * @param  string|null $label
     * @param  bool        $reload
     * @param  array       $showWhen
     * @return MenuItem
     */
    public function withDeleteForm(?string $label = null, bool $reload = true, array $showWhen = []): MenuItem
    {
        return $this->addItem('delete')
            ->icon('ico-trash')
            ->value(MetaFoxForm::ACTION_ROW_EDIT)
            ->label($label ?? __p('core::phrase.delete'))
            ->params([
                'action' => 'delete',
                'reload' => $reload,
            ])
            ->showWhen($showWhen);
    }
}
