<?php

namespace MetaFox\Profile\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Section;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Field.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface FieldRepositoryInterface
{
    /**
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewFields(array $attributes): Paginator;

    /**
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewFieldsInSectionSystem(array $attributes): Paginator;

    /**
     * @return Collection
     */
    public function getActiveFields(): Collection;

    /**
     * @param array<int> $orderIds
     *
     * @return bool
     */
    public function orderFields(array $orderIds): bool;

    /**
     * @param array $attributes
     *
     * @return Field
     */
    public function createField(array $attributes): Field;

    /**
     * @param array $attributes
     * @param int   $id
     *
     * @return Field
     */
    public function updateField(array $attributes, int $id): Field;

    /**
     * @param int   $id
     * @param array $attributes
     *
     * @return Field
     */
    public function toggleActive(int $id, array $attributes): Field;

    /**
     * @param ?User $user
     * @param array $attributes
     *
     * @return Builder
     */
    public function getBuildQuery(?User $user, array $attributes): Builder;

    /**
     * @param ?User $user
     * @param array $attributes
     *
     * @return Collection
     */
    public function getFieldsActiveCollectionByType(?User $user, array $attributes): Collection;

    /**
     * @param ?User   $user
     * @param Builder $query
     *
     * @return Builder
     */
    public function buildQueryRoles(?User $user, Builder $query): Builder;

    /**
     * @param User    $user
     * @param Section $section
     * @param array   $attributes
     *
     * @return array
     */
    public function getFieldsValueBySection(User $user, Section $section, array $attributes): array;

    /**
     * @param array $attributes
     *
     * @return Collection
     */
    public function getFieldRegistration(array $attributes): Collection;

    /**
     * @param string $sectionType
     *
     * @return Collection
     */
    public function getFieldSearch(string $sectionType): Collection;

    /**
     * @param array $types
     *
     * @return array
     */
    public function getFieldIdsByTypes(array $types): array;

    public function clearCache(): void;

    /**
     * @param int   $roleId
     * @param array $attributes
     *
     * @return Collection
     */
    public function getFieldsActiveByVisibleRole(int $roleId, array $attributes = []): Collection;

    /**
     * @param Builder  $query
     * @param int|null $roleId
     *
     * @return Builder
     */
    public function buildQueryVisibleRoles(Builder $query, ?int $roleId): Builder;

    /**
     * @param User  $resource
     * @param ?User $context
     * @param array $attributes
     *
     * @return Collection
     */
    public function getFieldCollections(User $resource, ?User $context, array $attributes): Collection;

    /**
     * @return SupportCollection
     */
    public function getFieldCollectionsByBasicInfoSection(): SupportCollection;

    /**
     * @param  string $fieldName
     * @return bool
     */
    public function isActiveField(string $fieldName): bool;
}
