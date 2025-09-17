<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Alert.
 */
class Alert extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::ALERT)
            ->variant('standard')
            ->fullWidth();
    }

    public function severity(string $severity): self
    {
        return $this->setAttribute('severity', $severity);
    }

    public function message(string $message): self
    {
        return $this->setAttribute('message', $message);
    }

    public function asSuccess(): self
    {
        return $this->severity('success');
    }

    public function asInfo(): self
    {
        return $this->severity('info');
    }

    public function asWarning(): self
    {
        return $this->severity('warning');
    }

    public function asError(): self
    {
        return $this->severity('error');
    }

    public function noIcon(): self
    {
        return $this->setAttribute('icon', false);
    }
}
