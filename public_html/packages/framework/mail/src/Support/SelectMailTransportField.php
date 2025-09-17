<?php

namespace MetaFox\Mail\Support;

use MetaFox\Form\Html\Choice;
use MetaFox\Platform\Facades\Settings;

/**
 * @driverType form-field
 * @driverName selectMailTransport
 */
class SelectMailTransportField extends Choice
{
    protected function prepare(): void
    {
        $mailer = Settings::get('mail.mailers', []);
        $options = [];

        foreach ($mailer as $name => $value) {

            // https://laravel.com/docs/9.x/mail#failover-configuration
            if ($name == 'failover') {
                continue;
            }

            $options[] = ['label' => $name, 'value' => $name];
        }

        $this->options($options);
    }
}
