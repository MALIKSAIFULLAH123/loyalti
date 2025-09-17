<?php

namespace MetaFox\Poll\Form\Mobile;

use MetaFox\Form\AbstractField;

/**
 * @driverName pollAnswer
 * @driverType form-field-mobile
 */
class PollAnswerField extends AbstractField
{
    public const COMPONENT = 'PollAnswer';

    public function initialize(): void
    {
        $this->setComponent(self::COMPONENT);
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
