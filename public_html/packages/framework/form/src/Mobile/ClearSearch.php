<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class ClearSearch.
 */
class ClearSearch extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_CLEAR_SEARCH);
    }

    public function excludeFields(array $exclude): self
    {
        return $this->setAttribute('excludeFields', $exclude);
    }

    public function align(string $align): self
    {
        return $this->setAttribute('align', $align);
    }

    public function targets(array $targets): self
    {
        return $this->setAttribute('targetFields', $targets);
    }
}
