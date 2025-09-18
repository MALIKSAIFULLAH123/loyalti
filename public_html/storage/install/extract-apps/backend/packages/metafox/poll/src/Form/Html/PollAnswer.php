<?php

namespace MetaFox\Poll\Form\Html;

use MetaFox\Form\AbstractField;

class PollAnswer extends AbstractField
{
    public const COMPONENT = 'PollAnswer';

    public function initialize(): void
    {
        $this->component(self::COMPONENT)
                ->variant('outlined')
                ->fullWidth()
                ->maxLength(255);
    }

    public function minAnswers(int $min): self
    {
        return $this->setAttribute('minAnswers', $min);
    }

    public function maxAnswers(int $max): self
    {
        return $this->setAttribute('maxAnswers', $max);
    }
}
