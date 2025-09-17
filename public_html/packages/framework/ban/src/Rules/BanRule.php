<?php

namespace MetaFox\Ban\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MetaFox\Ban\Facades\Ban;

abstract class BanRule implements ValidationRule
{
    abstract protected function getType(): string;

    abstract protected function defineFailedMessage(): string;

    protected ?string $currentValue;
    protected ?string $failedMessage;

    public function __construct(?string $currentValue = null)
    {
        $this->currentValue = $currentValue;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value == $this->currentValue) {
            return;
        }

        if (empty($value)) {
            return;
        }

        if (!$this->hasValidStructure($value)) {
            return;
        }

        if (Ban::validate($this->getType(), $value)) {
            return;
        }

        $fail($this->getFailedMessage());
    }

    protected function hasValidStructure(mixed $value): bool
    {
        return true;
    }

    public function setFailedMessage(string $message): ?self
    {
        $this->failedMessage = $message;

        return $this;
    }

    protected function getFailedMessage(): string
    {
        return $this->failedMessage ?? $this->defineFailedMessage();
    }
}
