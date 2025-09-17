<?php

namespace MetaFox\Event\Support\Export\Properties;

use DateTimeInterface;
use DateTimeZone;
use MetaFox\Event\Support\Export\DateTimeValue;

class DateTimeProperty extends AbstractProperty
{

    private DateTimeZone $dateTimeZone;

    public static function fromDateTime(
        string            $name,
        DateTimeInterface $dateTime,
        bool              $withTime = false,
        bool              $withoutTimeZone = false
    ): DateTimeProperty
    {
        return new self($name, new DateTimeValue($dateTime, $withTime), $withoutTimeZone);
    }

    public function __construct(protected string $name, protected DateTimeValue $dateTimeValue, protected bool $withoutTimeZone = false)
    {
        $this->dateTimeZone = $dateTimeValue->getDateTime()->getTimezone();

        if (!$withoutTimeZone && !$this->isUTC()) {
            $this->addParameter(new Parameter('TZID', $this->dateTimeZone->getName()));
        }

        if (!$dateTimeValue->hasTime()) {
            $this->addParameter(new Parameter('VALUE', 'DATE'));
        }
    }

    public function getValue(): string
    {
        return $this->isUTC() && $this->dateTimeValue->hasTime()
            ? "{$this->dateTimeValue->format()}Z"
            : $this->dateTimeValue->format();
    }

    public function getOriginalValue(): DateTimeInterface
    {
        return $this->dateTimeValue->getDateTime();
    }

    private function isUTC(): bool
    {
        return $this->dateTimeZone->getName() === 'UTC';
    }
}
