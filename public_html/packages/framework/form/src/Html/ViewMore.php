<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class ViewMore.
 */
class ViewMore extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_VIEW_MORE);
    }

    /**
     * @param array<string>
     * @return ViewMore
     */
    public function excludeFields(array $exclude): self
    {
        return $this->setAttribute('excludeFields', $exclude);
    }

    /**
     * @param  string   $align
     * @return ViewMore
     */
    public function align(string $align): self
    {
        return $this->setAttribute('align', $align);
    }

    /**
     * @param  string   $text
     * @return ViewMore
     */
    public function viewMoreText(string $text): self
    {
        return $this->setAttribute('moreText', $text);
    }

    /**
     * @param  string   $text
     * @return ViewMore
     */
    public function viewLessText(string $text): self
    {
        return $this->setAttribute('lessText', $text);
    }
}
