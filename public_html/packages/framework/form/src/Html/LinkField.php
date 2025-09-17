<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Html\Traits\CssTrait;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class LinkButtonField.
 */
class LinkField extends AbstractField
{
    use CssTrait;

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->component(MetaFoxForm::HTML_LINK);
    }

    /**
     * @param  string $name
     * @return $this
     */
    public function action(string $name): self
    {
        return $this->setAttribute('action', $name);
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function actionPayload(array $options): self
    {
        return $this->setAttribute('actionPayload', $options);
    }
}
