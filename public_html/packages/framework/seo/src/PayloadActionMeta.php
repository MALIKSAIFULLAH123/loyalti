<?php

namespace MetaFox\SEO;

use Illuminate\Support\Arr;

/**
 * Class PayloadActionMeta.
 */
final class PayloadActionMeta
{
    protected array $attributes = [];

    /**
     * @return PayloadActionMeta
     */
    public static function payload(): PayloadActionMeta
    {
        return new PayloadActionMeta();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute(string $name, mixed $value): PayloadActionMeta
    {
        Arr::set($this->attributes, $name, $value);

        return $this;
    }

    /**
     * @param string $formName
     *
     * @return $this
     */
    public function formName(string $formName): PayloadActionMeta
    {
        return $this->setAttribute('formName', $formName);
    }

    /**
     * @param string $processChildId
     *
     * @return $this
     */
    public function processChildId(string $processChildId): PayloadActionMeta
    {
        return $this->setAttribute('processChildId', $processChildId);
    }

    /**
     * @param string $previousProcessChildId
     *
     * @return $this
     */
    public function previousProcessChildId(string $previousProcessChildId): PayloadActionMeta
    {
        return $this->setAttribute('previousProcessChildId', $previousProcessChildId);
    }

    /**
     * @param array $params
     *
     * @return $this
     */
    public function params(array $params): PayloadActionMeta
    {
        return $this->setAttribute('params', $params);
    }

    public function target(string $target): PayloadActionMeta
    {
        return $this->setAttribute('target', $target);
    }

    public function url(string $url): PayloadActionMeta
    {
        return $this->setAttribute('url', $url);
    }

    public function replace(bool $flag): PayloadActionMeta
    {
        return $this->setAttribute('replace', $flag);
    }
}
