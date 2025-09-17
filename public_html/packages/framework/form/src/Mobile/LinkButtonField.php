<?php

namespace MetaFox\Form\Mobile;

use MetaFox\Form\AbstractField;

/**
 * Class LinkButtonField.
 *
 * @driverType form-field-mobile
 * @driverName linkButton
 */
class LinkButtonField extends AbstractField
{
    public const COMPONENT = 'LinkButton';

    public function initialize(): void
    {
        $this->setComponent(self::COMPONENT)
            ->color('primary')
            ->variant('text')
            ->sizeMedium()
            ->fullWidth(false);
    }

    /**
     * @param  string $color
     * @return $this
     */
    public function color(string $color): self
    {
        return $this->setAttribute('color', $color);
    }

    /**
     * @param  string $link
     * @return $this
     */
    public function link(string $link): self
    {
        return $this->setAttribute('link', $link);
    }

    public function routerName(string $router): self
    {
        return $this->setAttribute('routerName', $router);
    }

    public function actionName(string $action): self
    {
        return $this->setAttribute('actionName', $action);
    }
}
