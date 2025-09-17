<?php

namespace MetaFox\Platform\Support;

use ArrayAccess;
use Illuminate\Support\Arr;

class PageSeo implements ArrayAccess
{
    public array $attributtes;

    public function __construct(array $attributes)
    {
        $this->attributtes = $attributes;
    }

    /**
     * <code>
     *  $seo->addMeta('og:url', ['property'=> 'og:url', 'content'=> 'url']);
     * </code>.
     * @param  string $key
     * @param  array  $meta
     * @return static
     */
    public function addMeta(string $key, array $meta): static
    {
        Arr::set($this->attributtes, 'meta.' . $key, $meta);

        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributtes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): static
    {
        Arr::set($this->attributtes, $key, $value);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributtes;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributtes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributtes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        Arr::set($this->attributtes, $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributtes[$offset]);
    }
}
