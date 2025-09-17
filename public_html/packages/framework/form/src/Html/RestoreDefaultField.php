<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class RestoreDefaultField.
 */
class RestoreDefaultField extends AbstractField
{
    public function initialize(): void
    {
        $arrowConfig = [
            'icon' => 'ico-arrow-right',
        ];

        $this->component(MetaFoxForm::RESTORE_DEFAULT)
            ->arrow($arrowConfig);
    }

    /**
     * @param array<string, mixed> $target
     */
    public function from(array $target): self
    {
        return $this->setAttribute('from', $target);
    }

    /**
     * @param array<string, mixed> $target
     */
    public function to(array $target): self
    {
        return $this->setAttribute('to', $target);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function arrow(array $config = []): self
    {
        return $this->setAttribute('arrowConfig', $config);
    }
}
