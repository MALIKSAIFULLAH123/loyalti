<?php

namespace MetaFox\Log\Support;

use MetaFox\Form\Html\Choice;

/**
 * @driverType form-field
 * @driverName selectLogLevel
 */
class SelectLogLevelField extends Choice
{
    protected function prepare(): void
    {
        $this->options([
            ['value' => 'emergency', 'label' => 'emergency'],
            ['value' => 'alert', 'label' => 'alert'],
            ['value' => 'critical', 'label' => 'critical'],
            ['value' => 'error', 'label' => 'error'],
            ['value' => 'warning', 'label' => 'warning'],
            ['value' => 'notice', 'label' => 'notice'],
            ['value' => 'info', 'label' => 'info'],
            ['value' => 'debug', 'label' => 'debug'],
        ])->label(__p('log::phrase.log_level_label'));
    }
}
