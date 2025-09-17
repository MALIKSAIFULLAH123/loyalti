<?php

namespace MetaFox\Localize\Repositories;

use Illuminate\Support\Collection;
use MetaFox\Localize\Models\Timezone;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface TimeZone.
 * @mixin BaseRepository
 */
interface TimezoneRepositoryInterface
{
    /**
     * @return ?string
     */
    public function getName(?int $id);

    /**
     * @param  string|null   $name
     * @return Timezone|null
     */
    public function getTimezoneByName(?string $name): ?Timezone;

    /**
     * @return int
     */
    public function getDefaultTimezoneId(): int;

    /**
     * @return array<int, mixed>
     */
    public function getTimeZones(): array;

    /**
     * @return Collection
     */
    public function getActiveTimeZones(): Collection;

    /**
     * @return array
     */
    public function getActiveTimeZonesForForm(): array;

    /**
     * @return Timezone|null
     */
    public function getFirstActiveTimeZone(): ?Timezone;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isActive
     * @return bool
     */
    public function updateActive(User $context, int $id, bool $isActive): bool;
}
