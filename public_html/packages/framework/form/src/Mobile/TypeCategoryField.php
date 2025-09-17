<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class TypeCategoryField.
 */
class TypeCategoryField extends AbstractField
{
    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::COMPONENT_SELECT)
            ->setValue('outlined')
            ->fullWidth();
    }

    /**
     * @param  array<int, mixed> $data
     * @return $this
     */
    public function options(array $data): self
    {
        return $this->setAttribute('options', $data);
    }

    /**
     * @param  array<string,mixed> $data
     * @return $this
     */
    public function subOptions(array $data): self
    {
        return $this->setAttribute('suboptions', $data);
    }

    public function useSectionList(): self
    {
        return $this->setAttribute('useSectionList', true);
    }
}
