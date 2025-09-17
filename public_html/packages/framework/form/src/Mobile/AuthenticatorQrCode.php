<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Mobile;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Html\QrCode;

/**
 * Class AuthenticatorQrCode.
 */
class AuthenticatorQrCode extends QrCode
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::AUTH_QR_CODE);
    }
}
