<?php

namespace MetaFox\Event\Support\Export;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Support\Export\Properties\AppleLocationProperty;
use MetaFox\Event\Support\Export\Properties\DateTimeProperty;
use MetaFox\Event\Support\Export\Properties\LocationProperty;
use MetaFox\Event\Support\Export\Properties\TextProperty;

class Event extends AbstractCalendar
{
    private ?DateTimeValue $starts = null;

    private ?DateTimeValue $ends = null;

    private DateTimeValue $created;

    private ?string $name = null;

    private ?string $description = null;

    private ?string $address = null;

    private ?string $addressName = null;

    private ?string $googleConference = null;

    private ?string $microsoftTeams = null;

    private ?float $lat = null;

    private ?float $lng = null;

    private string $uuid;

    private bool $withoutTimezone = false;

    private bool $isFullDay = false;

    private ?bool $transparent = null;

    private ?string $status = null;

    public function __construct(string $name = null)
    {
        $this->name    = $name;
        $this->uuid    = uniqid();
        $this->created = (new DateTimeValue(new DateTimeImmutable()))
            ->convertToTimezone(new \DateTimeZone('UTC'));
    }

    public function getComponentType(): string
    {
        return 'VEVENT';
    }

    public function getRequiredProperties(): array
    {
        return [
            'UID',
            'DTSTAMP',
            'DTSTART',
        ];
    }

    public function startsAt(DateTimeInterface $starts, bool $withTime = true): self
    {
        $this->starts = new DateTimeValue($starts, $withTime);

        return $this;
    }

    public function endsAt(DateTimeInterface $ends, bool $withTime = true): Event
    {
        $this->ends = new DateTimeValue($ends, $withTime);

        return $this;
    }

    public function name(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function address(?string $address, string $name = null): self
    {
        $this->address = $address;

        if ($name) {
            $this->addressName = $name;
        }

        return $this;
    }

    public function addressName(?string $name): self
    {
        $this->addressName = $name;

        return $this;
    }

    public function googleConference(?string $googleConference): self
    {
        $this->googleConference = $googleConference;

        return $this;
    }

    public function microsoftTeams(?string $microsoftTeams): self
    {
        $this->microsoftTeams = $microsoftTeams;

        return $this;
    }

    public function coordinates(?float $lat, ?float $lng): self
    {
        $this->lat = $lat;
        $this->lng = $lng;

        return $this;
    }

    public function uniqueIdentifier(string $uid): self
    {
        $this->uuid = $uid;

        return $this;
    }

    public function createdAt(DateTimeInterface $created, bool $withTime = true): self
    {
        $this->created = (new DateTimeValue($created, $withTime))
            ->convertToTimezone(new DateTimeZone('UTC'));

        return $this;
    }

    public function withoutTimezone(): self
    {
        $this->withoutTimezone = true;

        return $this;
    }

    public function fullDay(): self
    {
        $this->isFullDay = true;

        return $this;
    }

    public function transparent(): self
    {
        $this->transparent = true;

        return $this;
    }

    public function status(int $status): self
    {
        $this->status = $this->statusEvents($status);

        return $this;
    }

    protected function payload(): Payload
    {
        $payload = new Payload($this->getComponentType());

        $this->resolveProperties($payload)
            ->resolveDateProperty($payload, $this->starts, 'DTSTART')
            ->resolveDateProperty($payload, $this->ends, 'DTEND')
            ->resolveLocationProperties($payload);

        return $payload;
    }

    protected function resolveProperties(Payload $payload): self
    {
        $payload->property(new TextProperty('UID', $this->uuid))
            ->property(new DateTimeProperty('DTSTAMP', $this->created, $this->withoutTimezone))
            ->optional(
                $this->name,
                fn() => new TextProperty('SUMMARY', $this->name)
            )
            ->optional(
                $this->description,
                fn() => new TextProperty('DESCRIPTION', $this->description)
            )
            ->optional(
                $this->address,
                fn() => new TextProperty('LOCATION', $this->address)
            )
            ->optional(
                $this->status,
                fn() => new TextProperty('STATUS', $this->status)
            )
            ->optional(
                $this->googleConference,
                fn() => new TextProperty('X-GOOGLE-CONFERENCE', $this->googleConference)
            )
            ->optional(
                $this->microsoftTeams,
                fn() => new TextProperty('X-MICROSOFT-SKYPETEAMSMEETINGURL', $this->microsoftTeams)
            )
            ->optional(
                $this->transparent,
                fn() => new TextProperty('TRANSP', 'TRANSPARENT')
            );
        return $this;
    }

    protected function resolveDateProperty(Payload $payload, ?DateTimeValue $value, string $name): self
    {
        if ($value === null) {
            return $this;
        }

        $payload->property(
            DateTimeProperty::fromDateTime($name, $value->getDateTime(), !$this->isFullDay, $this->withoutTimezone)
        );

        return $this;
    }

    protected function resolveLocationProperties(Payload $payload): self
    {
        if (is_null($this->lng) && is_null($this->lat)) {
            return $this;
        }

        $payload->property(new LocationProperty('GEO', $this->lat, $this->lng));

        if (is_null($this->address) || is_null($this->addressName)) {
            return $this;
        }

        $property = new AppleLocationProperty(
            $this->lat,
            $this->lng,
            $this->address,
            $this->addressName
        );

        $payload->property($property);

        return $this;
    }

    protected function statusEvents(int $status): string
    {
        $arrays = [
            Member::NOT_INTERESTED => 'CANCELLED',
            Member::JOINED         => 'CONFIRMED',
            Member::INTERESTED     => 'TENTATIVE',
        ];

        return $arrays[$status];
    }
}
