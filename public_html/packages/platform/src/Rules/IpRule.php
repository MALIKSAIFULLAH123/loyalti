<?php
namespace MetaFox\Platform\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MetaFox\Platform\MetaFoxConstant;

class IpRule implements ValidationRule
{
    public function __construct(protected string $ipv4Pattern = MetaFoxConstant::IP_ADDRESS_V4_REGEX_WILDCARD, protected string $ipv6Pattern = MetaFoxConstant::IP_ADDRESS_V6_REGEX_WILDCARD, protected ?string $errorMessage = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || trim($value) === '') {
            $fail($this->getErrorMessage());
            return;
        }

        if (preg_match('/^' . $this->getIpV4Pattern() . '$/', $value)) {
            return;
        }

        if (preg_match('/^' . $this->getIpV6Pattern() . '$/', $value)) {
            return;
        }

        $fail($this->getErrorMessage());
    }

    protected function getIpV4Pattern(): string
    {
        return $this->ipv4Pattern;
    }

    protected function getIpV6Pattern(): string
    {
        return $this->ipv6Pattern;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage ?? __p('ban::phrase.invalid_ip_address_format');
    }
}
