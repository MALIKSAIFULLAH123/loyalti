<?php

namespace MetaFox\Localize\Form\Html;

use MetaFox\Form\Html\Choice;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;

/**
 * @driverType form-field
 * @driverName selectLocale
 */
class SelectLocale extends Choice
{
    public function initialize(): void
    {
        parent::initialize();

        $array   = resolve(LanguageRepositoryInterface::class)->getOptions();
        $options = [];
        foreach ($array as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        $this->options($options);
    }
}
