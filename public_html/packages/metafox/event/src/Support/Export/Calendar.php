<?php

namespace MetaFox\Event\Support\Export;

use MetaFox\Event\Support\Export\Properties\Parameter;
use MetaFox\Event\Support\Export\Properties\TextProperty;

class Calendar extends AbstractCalendar
{
    private array $events = [];

    private array $timezones = [];

    private ?string $name = null;

    private ?string $description = null;

    private bool $withoutTimezone = false;

    private ?string $productIdentifier = null;

    private ?string $source = null;

    public static function create(string $name = null): self
    {
        return new self($name);
    }

    public function __construct(string $name = null)
    {
        $this->name = $name;
    }

    public function getComponentType(): string
    {
        return 'VCALENDAR';
    }

    public function getRequiredProperties(): array
    {
        return [
            'VERSION',
            'PRODID',
        ];
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function productIdentifier(string $identifier): self
    {
        $this->productIdentifier = $identifier;

        return $this;
    }

    /**
     */
    public function event($event): self
    {
        if (is_null($event)) {
            return $this;
        }

        $events = array_map(function ($eventToResolve) {
            if (!is_callable($eventToResolve)) {
                return $eventToResolve;
            }

            $newEvent = new Event();

            $eventToResolve($newEvent);

            return $newEvent;
        }, is_array($event) ? $event : [$event]);

        $this->events = array_merge($this->events, $events);

        return $this;
    }

    /**
     *
     */
    public function timezone($timezone): self
    {
        if (is_null($timezone)) {
            return $this;
        }

        $this->timezones = array_merge(
            $this->timezones,
            is_array($timezone) ? $timezone : [$timezone]
        );

        return $this;
    }

    /**
     * Identifies a location where a client can retrieve updated data for the calendar.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7986#section-5.7
     */
    public function source(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function get(): string
    {
        return $this->toString();
    }

    protected function payload(): Payload
    {
        $payload = new Payload($this->getComponentType());
        return $payload->property(new TextProperty('VERSION', '2.0'))
            ->property(new TextProperty('PRODID', $this->productIdentifier ?? 'spatie/icalendar-generator'))
            ->optional(
                $this->name,
                fn() => (new TextProperty('NAME', $this->name))->addAlias('X-WR-CALNAME')
            )
            ->optional(
                $this->description,
                fn() => (new TextProperty('DESCRIPTION', $this->description))->addAlias('X-WR-CALDESC')
            )
            ->optional(
                $this->source,
                fn() => (new TextProperty('SOURCE', $this->source))->addParameter(new Parameter('VALUE', 'URI'))
            )
            ->subComponent(...$this->resolveEvents());
    }

    private function resolveEvents(): array
    {
        if ($this->withoutTimezone === false) {
            return $this->events;
        }

        return array_map(
            fn(Event $event) => $event->withoutTimezone(),
            $this->events
        );
    }
}
