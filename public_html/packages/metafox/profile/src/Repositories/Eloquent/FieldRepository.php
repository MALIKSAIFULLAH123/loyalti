<?php

namespace MetaFox\Profile\Repositories\Eloquent;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Models\Value;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Repositories\OptionRepositoryInterface;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Profile\Repositories\ValueRepositoryInterface;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * class FieldRepository.
 *
 * @property Field $model
 * @method   Field getModel()
 * @method   Field find($id, $columns = ['*'])
 */
class FieldRepository extends AbstractRepository implements FieldRepositoryInterface
{
    public function model()
    {
        return Field::class;
    }

    protected function optionsRepository(): OptionRepositoryInterface
    {
        return resolve(OptionRepositoryInterface::class);
    }

    protected function roleRepository(): RoleRepositoryInterface
    {
        return resolve(RoleRepositoryInterface::class);
    }

    protected function sectionRepository(): SectionRepositoryInterface
    {
        return resolve(SectionRepositoryInterface::class);
    }

    protected function valueRepository(): ValueRepositoryInterface
    {
        return resolve(ValueRepositoryInterface::class);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewFields(array $attributes): Paginator
    {
        $query        = $this->buildQueryViewField($attributes);
        $tableSection = (new Section())->getTable();

        $query->where("$tableSection.is_system", 0);

        return $query->paginate($attributes['limit'] ?? 100);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewFieldsInSectionSystem(array $attributes): Paginator
    {
        $query = $this->buildQueryViewField($attributes);

        return $query->paginate($attributes['limit'] ?? 100);
    }

    /**
     * @param array $attributes
     *
     * @return Builder
     */
    private function buildQueryViewField(array $attributes): Builder
    {
        $name         = Arr::get($attributes, 'title');
        $required     = Arr::get($attributes, 'required');
        $active       = Arr::get($attributes, 'active');
        $roleId       = Arr::get($attributes, 'role_id');
        $sectionId    = Arr::get($attributes, 'section_id');
        $sectionType  = Arr::get($attributes, 'section_type');
        $tableSection = (new Section())->getTable();
        $tableField   = $this->getModel()->getTable();
        $query        = $this->getModel()->newModelQuery();

        if ($name) {
            $searchScope = new SearchScope($name, ['key']);
            $query       = $query->addScope($searchScope);
        }

        if ($sectionType) {
            $query->leftJoin($tableSection, "$tableSection.id", '=', "$tableField.section_id");
            $query = $this->sectionRepository()->buildQueryProfile($query, $attributes)
                ->where("$tableSection.is_active", MetaFoxConstant::IS_ACTIVE);
        }

        if ($roleId) {
            $query->where(function ($innerQuery) use ($roleId) {
                $innerQuery->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('role_id', '=', $roleId);
                });

                $innerQuery->orWhereDoesntHave('roles');
            });
        }

        if (null !== $active) {
            $query->where("$tableField.is_active", $active);
        }

        if (null !== $required) {
            $query->where("$tableField.is_required", $required);
        }

        if (null !== $sectionId) {
            $query->where("$tableField.section_id", $sectionId);
        }

        return $query->select("$tableField.*")->orderBy("$tableField.ordering");
    }

    /**
     * @return Collection
     */
    public function getActiveFields(): Collection
    {
        return Cache::rememberForever(
            $this->getCacheNameForActiveFields(),
            fn () => $this->getModel()
                ->newModelQuery()
                ->where('is_active', 1)
                ->get()
        );
    }

    public function isActiveField(string $fieldName): bool
    {
        $activeFieldNames = $this->getActiveFields()->pluck('field_name')->toArray();

        return in_array($fieldName, $activeFieldNames);
    }

    /**
     * @param array $orderIds
     *
     * @return bool
     */
    public function orderFields(array $orderIds): bool
    {
        $fields = Field::query()
            ->whereIn('id', $orderIds)
            ->get()
            ->keyBy('id');

        if (!$fields->count()) {
            return true;
        }

        $ordering = 1;

        foreach ($orderIds as $orderId) {
            $orderField = $fields->get($orderId);

            if (null === $orderField) {
                continue;
            }

            $orderField->update(['ordering' => $ordering++]);
        }
        $this->clearCache();

        return true;
    }

    /**
     * @param array $attributes
     *
     * @return Field
     * @throws \Exception
     */
    public function createField(array $attributes): Field
    {
        $currentOrdering   = $this->getModel()->newQuery()->max('ordering');
        $description       = Arr::get($attributes, 'description');
        $label             = Arr::get($attributes, 'label');
        $name              = Arr::get($attributes, 'field_name');
        $sectionId         = Arr::get($attributes, 'section_id');
        $validationMessage = Arr::get($attributes, CustomField::VALIDATION_MESSAGE, []);
        $options           = Arr::pull($attributes, 'options', []);
        $section           = $this->sectionRepository()->find($sectionId);

        Arr::forget($attributes, CustomField::VALIDATION_MESSAGE);
        Arr::set($attributes, 'ordering', ++$currentOrdering);
        Arr::set($attributes, 'key', sprintf(CustomField::FIELD_USER_TYPE_NAME, $section->getUserType(), $name));

        /** @var Field $field */
        $field = $this->getModel()->newModelInstance();

        $field->fill($attributes);
        $field->setDescriptionAttribute($description);
        $field->setLabelAttribute($label);
        $field->save();

        if (!empty($options)) {
            $this->handleOptionFields($field, $options);
        }

        $this->clearCache();

        CustomFieldFacade::createValidationErrorMessage($field, $validationMessage);

        return $field;
    }

    /**
     * @param array $attributes
     * @param int   $id
     *
     * @return Field
     */
    public function updateField(array $attributes, int $id): Field
    {
        $field             = $this->find($id);
        $options           = Arr::get($attributes, 'options', []);
        $name              = Arr::get($attributes, 'field_name');
        $section           = $field->section;
        $validationMessage = Arr::get($attributes, CustomField::VALIDATION_MESSAGE, []);
        Arr::forget($attributes, CustomField::VALIDATION_MESSAGE);
        Arr::set($attributes, 'key', sprintf(CustomField::FIELD_USER_TYPE_NAME, $section->getUserType(), $name));

        if (!empty($options)) {
            $this->handleOptionFields($field, $options);
            Arr::forget($attributes, 'options');
        }

        $field->setDescriptionAttribute($attributes['description']);
        $field->setLabelAttribute($attributes['label']);
        $field->update($attributes);
        $field->refresh();
        $this->clearCache();
        CustomFieldFacade::createValidationErrorMessage($field, $validationMessage);

        return $field;
    }

    /**
     * @param Field $field
     * @param array $attributes
     *
     * @return bool
     */
    protected function handleOptionFields(Field $field, array $attributes): bool
    {
        $newField = array_filter($attributes, function ($item) {
            if (isset($item['status'])) {
                return $item['status'] == MetaFoxConstant::FILE_NEW_STATUS;
            }

            return false;
        });

        $removedField = array_filter($attributes, function ($item) {
            if (isset($item['status'])) {
                return $item['status'] == MetaFoxConstant::FILE_REMOVE_STATUS;
            }

            return false;
        });

        $updatedField = array_filter($attributes, function ($item) {
            if (isset($item['status'])) {
                return $item['status'] == MetaFoxConstant::FILE_UPDATE_STATUS;
            }

            return false;
        });

        if (!empty($newField)) {
            $this->optionsRepository()->createOptions($field, $newField);
        }

        if (!empty($removedField)) {
            $this->optionsRepository()->removeOptions($field, $removedField);
        }

        if (!empty($updatedField)) {
            $this->optionsRepository()->updateOptions($field, $updatedField);
        }

        return true;
    }

    /**
     * @param int   $id
     * @param array $attributes
     *
     * @return Field
     */
    public function toggleActive(int $id, array $attributes): Field
    {
        $field = $this->find($id);

        $field->update($attributes);
        $this->clearCache();

        return $field;
    }

    /**
     * @param ?User $user
     * @param array $attributes
     *
     * @return Collection
     */
    public function getFieldsActiveCollectionByType(?User $user, array $attributes): Collection
    {
        $sectionType = Arr::get($attributes, 'section_type', CustomField::SECTION_TYPE_USER);
        $roleId      = null;

        if ($sectionType == CustomField::SECTION_TYPE_USER) {
            $contextRole = $this->roleRepository()->roleOf($user);
            $roleId      = $contextRole->entityId();
        }

        $cacheName = $this->getCacheNameForFieldsActive($sectionType, $roleId);

        return Cache::rememberForever($cacheName, function () use ($user, $attributes) {
            $sectionType  = Arr::get($attributes, 'section_type');
            $tableSection = (new Section())->getTable();
            $tableField   = $this->getModel()->getTable();
            $query        = $this->getBuildQuery($user, []);

            $query->select("$tableField.*");
            $query->leftJoin($tableSection, "$tableSection.id", '=', "$tableField.section_id");
            $query = $this->sectionRepository()->buildQueryProfile($query, $attributes)
                ->where("$tableSection.is_active", MetaFoxConstant::IS_ACTIVE);

            if ($sectionType == CustomField::SECTION_TYPE_USER) {
                $query = $this->buildQueryRoles($user, $query);
            }

            return $query->orderBy("$tableField.ordering")->get();
        });
    }

    /**
     * @param ?User $user
     * @param array $attributes
     *
     * @return Builder
     */
    public function getBuildQuery(?User $user, array $attributes): Builder
    {
        $tableField = $this->getModel()->getTable();
        $sectionId  = Arr::get($attributes, 'section_id');
        $query      = $this->getModel()->newQuery()
            ->where("$tableField.is_active", MetaFoxConstant::IS_ACTIVE);

        if ($sectionId > 0) {
            $query->where("$tableField.section_id", $sectionId);
        }

        return $query;
    }

    /**
     * @param ?User   $user
     * @param Builder $query
     *
     * @return Builder
     */
    public function buildQueryRoles(?User $user, Builder $query): Builder
    {
        $contextRole = $this->roleRepository()->roleOf($user);

        return $query->where(function (Builder $whereQuery) use ($contextRole) {
            $whereQuery->doesntHave('roles')
                ->orWhereHas('roles', function (Builder $hasQuery) use ($contextRole) {
                    $hasQuery->where('role_id', '=', $contextRole->entityId());
                });
        });
    }

    /**
     * @param User    $user
     * @param Section $section
     * @param array   $attributes
     *
     * @return array
     */
    public function getFieldsValueBySection(User $user, Section $section, array $attributes): array
    {
        $fields  = [];
        $context = Auth::user();
        Arr::set($attributes, 'section_id', $section->entityId());
        $fieldIds = $this->getFieldCollections($user, $context, $attributes)->pluck('id')->toArray();

        if (empty($fieldIds)) {
            return $fields;
        }

        $valuesCollection = $this->valueRepository()->getValuesByFieldIds($user, $fieldIds);

        if ($valuesCollection->isEmpty()) {
            return $fields;
        }

        foreach ($valuesCollection as $value) {
            /**
             * @var Value $value
             */
            $params = $this->getValuesForView($value, $attributes);

            if (null === $params) {
                continue;
            }

            $fields[$value->field->name] = $params;
        }

        return $fields;
    }

    protected function getValuesForView(Value $value, array $attributes = []): ?array
    {
        $field = $value->field;

        if (!$field instanceof Field) {
            return null;
        }

        $fieldValue = $this->valueRepository()->handleFieldValue($field, $value, $attributes);

        if ($fieldValue === null) {
            return null;
        }

        $params = [
            'label'       => $field->label,
            'description' => $field->description,
            'type'        => $field->edit_type,
            'value'       => $fieldValue,
            'value_text'  => $fieldValue,
        ];

        return $this->transformValuesByType($field->edit_type, $params);
    }

    protected function transformValuesByType(string $editType, array $params): array
    {
        if (CustomField::CHECK_BOX === $editType) {
            $value = (bool) Arr::get($params, 'value');

            $text = match ($value) {
                true  => __p('core::phrase.yes'),
                false => __p('core::phrase.no'),
            };

            return array_merge($params, [
                'value'      => $value,
                'value_text' => $text,
                'format'     => 'L',
            ]);
        }

        if (CustomField::BASIC_DATE === $editType) {
            return array_merge($params, [
                'valueFormat' => CustomField::BASIC_DATE_CLIENT_FORMAT,
                'as'          => CustomField::BASIC_DATE_RENDER_AS,
            ]);
        }

        if (CustomField::MULTI_CHOICE === $editType) {
            if (is_array($value = Arr::get($params, 'value'))) {
                Arr::set($params, 'value_text', implode(', ', $value));
            }

            if ((!MetaFox::isMobile() || version_compare(MetaFox::getApiVersion(), 'v1.16', '>='))) {
                Arr::set($params, 'as', 'Tag');
            }

            return $params;
        }

        return $params;
    }

    /**
     * @param array $attributes
     *
     * @return Collection
     */
    public function getFieldRegistration(array $attributes): Collection
    {
        $tableField = $this->getModel()->getTable();
        $query      = $this->buildQueryFields(['section_type' => CustomField::SECTION_TYPE_USER]);

        $roleId = Arr::get($attributes, 'role_id');

        return $query->where("$tableField.is_register", MetaFoxConstant::IS_ACTIVE)
            ->where(function (Builder $whereQuery) use ($roleId) {
                $whereQuery->orDoesntHave('roles');
                $whereQuery->orWhereHas('roles', function (Builder $hasQuery) use ($roleId) {
                    if ($roleId) {
                        $hasQuery->where('role_id', $roleId);
                    }
                });
            })->get();
    }

    /**
     * @param string $sectionType
     *
     * @return Collection
     * @throws AuthenticationException
     */
    public function getFieldSearch(string $sectionType): Collection
    {
        $tableField   = $this->getModel()->getTable();
        $tableSection = (new Section())->getTable();
        $query        = $this->buildQueryFields(['section_type' => $sectionType]);

        $query->where("$tableSection.is_system", 0);

        $context = Auth::user();

        if (!$context?->hasSuperAdminRole()) {
            $contextRole = $this->roleRepository()->roleOf($context);
            $roleId      = $contextRole->entityId();
            $query       = $this->buildQueryVisibleRoles($query, $roleId);
        }

        return $query->where("$tableField.is_search", MetaFoxConstant::IS_ACTIVE)->get();
    }

    /**
     * @param array $attributes
     *
     * @return Builder
     */
    protected function buildQueryFields(array $attributes): Builder
    {
        $tableSection = (new Section())->getTable();
        $tableField   = $this->getModel()->getTable();
        $query        = $this->getModel()->newQuery();

        $query->select("$tableField.*")
            ->where("$tableField.is_active", MetaFoxConstant::IS_ACTIVE);

        $query->leftJoin($tableSection, "$tableSection.id", '=', "$tableField.section_id")
            ->where("$tableSection.is_active", MetaFoxConstant::IS_ACTIVE)
            ->orderBy("$tableField.ordering");

        return $this->sectionRepository()->buildQueryProfile($query, $attributes);
    }

    /**
     * @param array $types
     *
     * @return array
     */
    public function getFieldIdsByTypes(array $types): array
    {
        return $this->getModel()->newQuery()
            ->where('is_active', MetaFoxConstant::IS_ACTIVE)
            ->whereIn('edit_type', $types)
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param string   $sectionType
     * @param int|null $roleId
     *
     * @return string
     */
    protected function getCacheNameForFieldsActive(string $sectionType, ?int $roleId = null): string
    {
        return match ($sectionType) {
            CustomField::SECTION_TYPE_USER => 'fields_active_by_user_role_' . $roleId,
            default                        => 'fields_active_by_' . $sectionType
        };
    }

    /**
     * @param int $roleId
     *
     * @return string
     */
    protected function getCacheNameForFieldsActiveByVisibleRole(int $roleId): string
    {
        return 'fields_active_by_user_visible_role_' . $roleId;
    }

    protected function getCacheNameForActiveFields(): string
    {
        return 'active_fields';
    }

    public function clearCache(): void
    {
        $roles = $this->roleRepository()->getRoleOptions();
        foreach (CustomFieldFacade::getAllowSectionType() as $sectionType) {
            Cache::forget($this->getCacheNameForFieldsActive($sectionType, 0));
        }

        foreach ($roles as $role) {
            Cache::forget($this->getCacheNameForFieldsActive(CustomField::SECTION_TYPE_USER, $role['value']));
            Cache::forget($this->getCacheNameForFieldsActiveByVisibleRole($role['value']));
        }

        Cache::forget($this->getCacheNameForActiveFields());
    }

    public function getFieldsActiveByVisibleRole(int $roleId, array $attributes = []): Collection
    {
        $cacheName = $this->getCacheNameForFieldsActiveByVisibleRole($roleId);
        $query     = $this->buildQueryFields($attributes);

        return Cache::rememberForever($cacheName, function () use ($roleId, $query) {
            $query = $this->buildQueryVisibleRoles($query, $roleId);

            return $query->get();
        });
    }

    /**
     * @param ?int    $roleId
     * @param Builder $query
     *
     * @return Builder
     */
    public function buildQueryVisibleRoles(Builder $query, ?int $roleId): Builder
    {
        return $query->where(function (Builder $whereQuery) use ($roleId) {
            $whereQuery->doesntHave('visibleRoles')
                ->orWhereHas('visibleRoles', function (Builder $hasQuery) use ($roleId) {
                    $hasQuery->where('role_id', '=', $roleId);
                });
        });
    }

    /**
     * @param User  $resource
     * @param ?User $context
     * @param array $attributes
     *
     * @return Collection
     */
    public function getFieldCollections(User $resource, ?User $context, array $attributes): Collection
    {
        $collections = $this->getFieldsActiveCollectionByType($resource, $attributes);
        $sectionId   = Arr::get($attributes, 'section_id');
        $collections = $sectionId ? $collections->where('section_id', $sectionId) : $collections;

        if ($context instanceof User && CustomFieldFacade::checkVisibleRole($context, $resource)) {
            $contextRole = $this->roleRepository()->roleOf($context);
            $collections = $this->getFieldsActiveByVisibleRole($contextRole->entityId(), $attributes)
                ->whereIn('id', $collections->pluck('id')->toArray());
        }

        return $collections;
    }

    /**
     * @return SupportCollection
     */
    public function getFieldCollectionsByBasicInfoSection(): SupportCollection
    {
        $basicSection = $this->sectionRepository()->getSectionByName('basic_info');

        if (!$basicSection instanceof Section) {
            return collect([]);
        }

        $tableField = $this->getModel()->getTable();
        $query      = $this->getModel()->newQuery()
            ->where("$tableField.is_active", MetaFoxConstant::IS_ACTIVE)
            ->where("$tableField.section_id", $basicSection->id);

        return $query->orderBy("$tableField.ordering")->get();
    }
}
