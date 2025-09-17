<?php

namespace MetaFox\Profile\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\FieldRoleData;
use MetaFox\Profile\Models\Section;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Profile\Support\CustomField;

/**
 * Class SectionRepository.
 * @method Section getModel()
 * @method Section find($id, $columns = ['*'])
 */
class SectionRepository extends AbstractRepository implements SectionRepositoryInterface
{
    public function model()
    {
        return Section::class;
    }

    /**
     * @inheritDoc
     */
    public function getAllSectionForForm(): array
    {
        $data     = [];
        $sections = $this->getModel()->newQuery()->get();
        foreach ($sections as $section) {
            /* @var Section $section */
            $data[] = [
                'value' => $section->entityId(),
                'label' => $section->label,
            ];
        }

        return $data;
    }

    public function deleteOrMoveToNewSection(User $user, array $attribute): bool
    {
        $sectionId    = $attribute['section_id'];
        $newSectionId = $attribute['new_section_id'] ?? 0;
        $section      = $this->find($sectionId);

        if ($newSectionId > 0) {
            //move to new section
            $section->fields()->update(['section_id' => $newSectionId]);

            return (bool) $section->delete();
        }

        $section->fields()->delete();

        return (bool) $section->delete();
    }

    public function orderSections(array $orderIds): bool
    {
        $fields = Section::query()
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

        return true;
    }

    public function createSection(array $attributes): Section
    {
        $currentOrdering        = $this->getModel()->newQuery()->max('ordering');
        $attributes['ordering'] = ++$currentOrdering;

        Arr::set($attributes['profiles'], 'profile_id', Arr::get($attributes, 'profile_id'));

        $field = $this->getModel()->newModelInstance();
        $field->fill($attributes);
        $field->save();

        return $field;
    }

    public function buildQuerySection(User $user, array $attribute): Builder
    {
        $tableField     = (new Field())->getTable();
        $tableFieldRole = (new FieldRoleData())->getTable();
        $sectionType    = Arr::get($attribute, 'section_type', CustomField::SECTION_TYPE_USER);
        $tableSection   = $this->getModel()->getTable();

        $query = $this->getModel()->newQuery()
            ->select("$tableSection.*")
            ->distinct()
            ->where("$tableSection.is_active", MetaFoxConstant::IS_ACTIVE)
            ->leftJoin($tableField, function (JoinClause $joinClause) use ($tableField, $tableSection) {
                $joinClause->on("$tableField.section_id", '=', "$tableSection.id");
            })->leftJoin($tableFieldRole, function (JoinClause $joinClause) use ($tableField, $tableFieldRole) {
                $joinClause->on("$tableFieldRole.field_id", '=', "$tableField.id");
            })
            ->where("$tableField.is_active", MetaFoxConstant::IS_ACTIVE)
            ->whereNotNull("$tableField.id");

        $query = $this->buildQueryProfile($query, $attribute);

        if ($sectionType == CustomField::SECTION_TYPE_USER) {
            $query->where(function (Builder $builder) use ($user, $tableFieldRole) {
                $builder->whereNull("$tableFieldRole.role_id");
                $contextRole = resolve(RoleRepositoryInterface::class)->roleOf($user);

                if ($contextRole instanceof Role) {
                    $builder->orWhere("$tableFieldRole.role_id", '=', $contextRole->entityId());
                }
            });
        }

        return $query->orderBy("$tableSection.ordering");
    }

    /**
     * @inheritDoc
     */
    public function viewSections(User $user, array $attribute): Paginator
    {
        $limit        = Arr::get($attribute, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $name         = Arr::get($attribute, 'title');
        $active       = Arr::get($attribute, 'active');
        $tableSection = $this->getModel()->getTable();
        $query        = $this->getModel()->newQuery()->select("$tableSection.*");
        $query        = $this->buildQueryProfile($query, $attribute);

        if (null !== $active) {
            $query->where("$tableSection.is_active", $active);
        }

        if ($name) {
            $query->where("$tableSection.name", $name);
        }

        return $query->orderBy("$tableSection.ordering")->simplePaginate($limit);
    }

    public function buildQueryProfile(Builder $query, array $attribute): Builder
    {
        $userType = Arr::get($attribute, 'section_type', CustomField::SECTION_TYPE_USER);
        $table    = $this->getModel()->getTable();

        return $query->leftJoin('user_custom_structure', function (JoinClause $joinClause) use ($table) {
            $joinClause->on('user_custom_structure.section_id', '=', $table . ".id");
        })->leftJoin('user_custom_profiles', function (JoinClause $joinClause) {
            $joinClause->on('user_custom_profiles.id', '=', 'user_custom_structure.profile_id');
        })->where("user_custom_profiles.user_type", '=', $userType);
    }

    /**
     * @inheritDoc
     */
    public function getSectionByTypeForForm(string $type): array
    {
        $data     = [];
        $table    = $this->getModel()->getTable();
        $query    = $this->getModel()->newQuery();
        $sections = $this->buildQueryProfile($query, [
            'section_type' => $type,
        ])->where("$table.is_active", MetaFoxConstant::IS_ACTIVE)
            ->where("$table.is_system", MetaFoxConstant::IS_INACTIVE)
            ->select("$table.*")->get();

        foreach ($sections as $section) {
            /* @var Section $section */
            $data[] = [
                'value' => $section->entityId(),
                'label' => $section->label,
            ];
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function toggleActive(int $id, array $attributes): Section
    {
        $section = $this->find($id);

        $section->update($attributes);

        return $section;
    }

    /**
     * @inheritDoc
     */
    public function getSectionByName(string $name): ?Section
    {
        $section = $this->getModel()->newQuery()->where("name", $name)->first();

        if (!$section instanceof Section) {
            return null;
        }
        
        return $section;
    }
}
