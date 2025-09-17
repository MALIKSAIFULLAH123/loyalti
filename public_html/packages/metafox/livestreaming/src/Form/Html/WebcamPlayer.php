<?php

namespace MetaFox\LiveStreaming\Form\Html;

use MetaFox\Form\AbstractField;

class WebcamPlayer extends AbstractField
{
    public const COMPONENT = 'WebcamPlayer';

    public function initialize(): void
    {
        $this->component(self::COMPONENT)
            ->name('webcam_player');
    }
}
