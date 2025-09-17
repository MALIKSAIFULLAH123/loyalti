<?php
namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

class BasicDateField extends AbstractField
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->component(MetaFoxForm::DATE_BASIC)
            ->valueFormat('DD/MM/YYYY');
    }

    /**
     * @param string $format
     * @return self
     */
    public function valueFormat(string $format): self
    {
        return $this->setAttribute('valueFormat', $format);
    }
}
