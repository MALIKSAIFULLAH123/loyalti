<?php

namespace MetaFox\Yup;

/**
 * Class StringType.
 * @link     @link https://dev-docs.metafoxapp.com/frontend/validation#string
 * @mixin MixedShape
 * @category framework
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class StringShape extends MixedShape
{
    /**
     * StringType constructor.
     */
    public function __construct()
    {
        $this->setAttribute('type', 'string');
    }

    /**
     * @param int|array{ref:string} $min
     * @param string|null           $error
     *
     * @return $this
     */
    public function minLength($min, ?string $error = null): self
    {
        if (!$error && !is_array($min)) {
            $error = __p('validation.minimum_string_length_description', ['min' => $min]);
        }

        return $this->setAttribute('minLength', $min, $error);
    }

    /**
     * @param int|array{ref:string} $min
     * @param string|null           $error
     *
     * @return $this
     */
    public function maxLength(int|array $min, ?string $error = null): self
    {
        if (!$error && !is_array($min)) {
            $error = __p('validation.maximum_string_length_description', ['min' => $min]);
        }

        return $this->setAttribute('maxLength', $min, $error);
    }

    /**
     * @param string|null $error
     *
     * @return $this
     */
    public function lowercase(?string $error = null): self
    {
        return $this->setAttribute('case', 'lowercase', $error);
    }

    /**
     * @param string|null $error
     *
     * @return $this
     */
    public function uppercase(?string $error = null): self
    {
        return $this->setAttribute('case', 'uppercase', $error);
    }

    /**
     * Require email format.
     *
     * @param string|null $error
     *
     * @return $this
     */
    public function email(?string $error = null): self
    {
        return $this->setAttribute('format', 'email', $error);
    }

    /**
     * Require URL format.
     *
     * @param string|null $error
     *
     * @return $this
     */
    public function url(?string $error = null): self
    {
        return $this->setAttribute('format', 'url', $error);
    }

    /**
     * @param string      $regex
     * @param string|null $error
     * @param bool        $excludeEmptyString
     *
     * @return $this
     */
    public function matches(string $regex, ?string $error = null, bool $excludeEmptyString = true): self
    {
        return $this->setAttribute('matches', [
            'regex'              => $regex,
            'excludeEmptyString' => $excludeEmptyString,
        ], $error);
    }

    /**
     * @param  array<mixed> $regexes
     * @param  string|null  $error
     * @param  bool         $excludeEmptyString
     * @return $this
     */
    public function matchesArray(array $regexes, ?string $error = null, bool $excludeEmptyString = true): self
    {
        return $this->setAttribute('matches', [
            'regex'              => $regexes,
            'excludeEmptyString' => $excludeEmptyString,
        ], $error);
    }

    /**
     * @param  string|null $error
     * @return $this
     */
    public function matchesAsNumeric(?string $error = null, bool $excludeEmptyString = true): self
    {
        return $this->matches('^[0-9]*$', $error, $excludeEmptyString);
    }
}
