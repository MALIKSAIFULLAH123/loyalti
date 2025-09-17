<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class QrCode.
 */
class QrCode extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::QR_CODE);
    }

    public function content(string $content): self
    {
        return $this->setAttribute('content', $content);
    }
}
