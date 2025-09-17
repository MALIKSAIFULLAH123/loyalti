<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Birthday.
 * @method self   setDateFrom(string $value)
 * @method self   setDateTo(string $value)
 * @method string getDateFrom()
 * @method string getDateTo()
 */
class Birthday extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::BIRTHDAY)
            ->variant('outlined')
            ->fullWidth();
    }
}
