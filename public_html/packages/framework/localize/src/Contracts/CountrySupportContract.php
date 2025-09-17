<?php

namespace MetaFox\Localize\Contracts;

use MetaFox\Platform\Contracts\User;

/**
 * Interface CountrySupportContract.
 */
interface CountrySupportContract
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAllActiveCountries(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getCountries(): array;

    /**
     * @param ?string $countryIso
     *
     * @return array<string, mixed>|null
     */
    public function getCountry(?string $countryIso): ?array;

    /**
     * @param string|null $countryIso
     *
     * @return string|null
     */
    public function getCountryName(?string $countryIso): ?string;

    /**
     * @return array<int, mixed>
     */
    public function buildCountrySearchForm(): array;

    /**
     * @param ?string $countryIso
     *
     * @return array<string, array<string, mixed>>|null
     */
    public function getCountryStates(?string $countryIso): ?array;

    /**
     * @param string|null $countryIso
     * @param string|null $stateIso
     *
     * @return string|null
     */
    public function getCountryStateName(?string $countryIso, ?string $stateIso): ?string;

    /**
     * @return string
     */
    public function getDefaultCountryIso(): string;

    /**
     * @return string
     */
    public function getDefaultCountryStateIso(): string;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $params
     * @return array<int, mixed>
     */
    public function getStatesSuggestions(User $context, array $params): array;

    /**
     * @param  string|array<string>|null                $pluck
     * @return array<string, mixed> | array<int, mixed>
     */
    public function getAllActiveStates(mixed $pluck = null): array;
}
