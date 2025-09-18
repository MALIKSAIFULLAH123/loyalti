<?php

namespace MetaFox\TourGuide\Form\Html;

use MetaFox\Form\AbstractField;

class DraggableBox extends AbstractField
{
    public const COMPONENT = 'DraggableBox';

    public function initialize(): void
    {
        $this->component(self::COMPONENT)
            ->name('draggable_box');
    }
}
