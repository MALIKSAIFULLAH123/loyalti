<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class TitleField.
 */
class TitleField extends AbstractField
{
    public function initialize(): void
    {
        $this->name('title')
            ->component(MetaFoxForm::TEXT)
            ->required()
            ->maxLength(255)
            ->label(__p('core::phrase.title'));
    }
}
