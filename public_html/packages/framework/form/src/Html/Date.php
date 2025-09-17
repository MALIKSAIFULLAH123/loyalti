<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class Date.
 */
class Date extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::DATE)
            ->variant('outlined');
    }

    public function minDate(string $min): self
    {
        return $this->setAttribute('minDate', $min);
    }

    public function maxDate(?string $max = null): self
    {
        if (is_string($max)) {
            $this->setAttribute('maxDate', $max);
        }

        return $this;
    }

    public function startOfDay(bool $value = true): self
    {
        return $this->setAttribute('startOfDay', $value);
    }

    public function endOfDay(bool $value = true): self
    {
        return $this->setAttribute('endOfDay', $value);
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

    /**
     * In case we need to use another UI for date component.
     * @param  array $values
     * @return $this
     */
    public function views(array $values): static
    {
        return $this->setAttribute('views', $values);
    }
}
