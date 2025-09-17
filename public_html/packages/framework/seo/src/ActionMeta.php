<?php

namespace MetaFox\SEO;

use Illuminate\Support\Arr;

/**
 * Class ActionMeta.
 */
class ActionMeta
{
    protected array $attributes = [];
    protected array $action     = [];

    public function __construct(protected string $actionName = 'continueAction')
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $this->setAttribute($this->actionName, $this->action);

        return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute(string $name, mixed $value): static
    {
        Arr::set($this->attributes, $name, $value);

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setActionAttribute(string $name, mixed $value): static
    {
        Arr::set($this->action, $name, $value);

        return $this;
    }

    public function continueAction(): static
    {
        return $this;
    }

    public function nextAction(): static
    {
        self::__construct('nextAction');

        return $this;
    }

    /**
     * @param PayloadActionMeta $payload
     *
     * @return $this
     */
    public function payload(PayloadActionMeta $payload): static
    {
        return $this->setActionAttribute('payload', $payload->toArray());
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function type(string $type): static
    {
        return $this->setActionAttribute('type', $type);
    }

}
