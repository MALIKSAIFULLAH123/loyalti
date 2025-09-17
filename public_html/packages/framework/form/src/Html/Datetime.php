<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Datetime.
 */
class Datetime extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::DATETIME)
            ->variant('outlined');
    }

    public function labelDatePicker(string $label): self
    {
        return $this->setAttribute('labelDatePicker', $label);
    }

    public function labelTimePicker(string $label): self
    {
        return $this->setAttribute('labelTimePicker', $label);
    }

    public function timeSuggestion(bool $flag = true): self
    {
        return $this->setAttribute('timeSuggestion', $flag);
    }

    public function minDateTime(string $min): self
    {
        return $this->setAttribute('minDateTime', $min);
    }

    public function displayFormat(string $value): self
    {
        return $this->setAttribute('displayFormat', $value);
    }

    public function timeFormat(int $value): self
    {
        return $this->setAttribute('timeFormat', $value);
    }

    /**
     * Setup for horizontal form.
     * @return $this
     */
    public function forAdminSearchForm(): static
    {
        return $this->sizeSmall()
            ->marginDense()
            ->maxWidth('220px');
    }

    public function nullable(bool $value = true): static
    {
        return $this->setAttribute('nullable', $value);
    }
}
