<?php

namespace MetaFox\Event\Support\Export\Properties;

use MetaFox\Event\Support\Export\DateTimeValue;

class Parameter
{
    public function __construct(protected string $name, protected mixed $value, protected bool $disableEscaping = false)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        $value = $this->valueToString();

        if ($this->disableEscaping) {
            return $value;
        }

        $replacements = [
            '\\' => '\\\\',
            '"'  => '\\"',
            ','  => '\\,',
            ';'  => '\\;',
            "\n" => '\\n',
        ];

        return str_replace(array_keys($replacements), $replacements, $value);
    }

    private function valueToString(): string
    {
        if (is_bool($this->value)) {
            $bool = $this->value ? 'TRUE' : 'FALSE';

            return "BOOLEAN:{$bool}";
        }

        if ($this->value instanceof DateTimeValue) {
            return $this->value->hasTime()
                ? "DATE-TIME:{$this->value->format()}"
                : "DATE:{$this->value->format()}";
        }

        return $this->value;
    }
}
