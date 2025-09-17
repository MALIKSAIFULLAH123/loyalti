<?php

namespace MetaFox\User\Support\Browse\Scopes\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

class CustomFieldScope extends BaseScope
{
    protected string $currentTable;
    protected string $sectionType;

    public function getSectionType(): string
    {
        return $this->sectionType;
    }

    public function setSectionType(string $sectionType = CustomField::SECTION_TYPE_USER): void
    {
        $this->sectionType = $sectionType;
    }

    public function getCurrentTable(): string
    {
        return $this->currentTable;
    }

    public function setCurrentTable(string $currentTable): void
    {
        $this->currentTable = $currentTable;
    }

    private function getAllowCustomFieldIds(): array
    {
        return self::fieldRepository()->getFieldSearch($this->getSectionType())
            ->pluck('id')->toArray();
    }

    /**
     * @var array
     */
    private array $customFields = [];

    /**
     * @param array $customFields
     * @return CustomFieldScope
     */
    public function setCustomFields(array $customFields): self
    {
        $this->customFields = $customFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomFields(): array
    {
        return $this->customFields;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $customFields        = $this->getCustomFields();
        $allowCustomFieldIds = $this->getAllowCustomFieldIds();

        foreach ($customFields as $field) {
            $fieldId    = Arr::get($field, 'id');
            $fieldValue = Arr::get($field, 'value');

            if (!in_array($fieldId, $allowCustomFieldIds)) {
                continue;
            }

            $this->handleSearchCustomFields($builder, $fieldId, $fieldValue);
        }
    }

    private function handleSearchCustomFields(Builder $builder, int $fieldId, string $fieldValue): void
    {
        $customValueAlias     = 'ucv_' . $fieldId;
        $customFieldAlias     = 'ufv_' . $fieldId;
        $customFieldDataAlias = 'ucfd_' . $fieldId;
        $currentTable         = $this->getCurrentTable();

        $builder->join('user_custom_value as ' . $customValueAlias, function (JoinClause $join) use ($customValueAlias, $currentTable) {
            $join->on($customValueAlias . '.user_id', '=', "$currentTable.id");
        });

        $builder->where($customValueAlias . '.field_id', $fieldId);
        $builder->where($customValueAlias . '.field_value_text', $this->likeOperator(), '%' . $fieldValue . '%');

        $builder->join('user_custom_fields as ' . $customFieldAlias, function (JoinClause $join) use ($customFieldAlias, $customValueAlias) {
            $join->on($customFieldAlias . '.id', '=', $customValueAlias . '.field_id');
        });

        if ($this->isSearchInOptionData($fieldId)) {
            $builder->join(
                'user_custom_option_data as ' . $customFieldDataAlias,
                function (JoinClause $joinClause) use ($customValueAlias, $customFieldDataAlias, $fieldValue) {
                    $joinClause->on($customValueAlias . '.id', '=', $customFieldDataAlias . '.item_id');
                    $joinClause->where($customFieldDataAlias . '.custom_option_id', $fieldValue);
                }
            );
        }
    }

    protected function isSearchInOptionData(int $id): bool
    {
        $fieldIds = CustomFieldFacade::getFieldIdsByTypes([CustomField::MULTI_CHOICE]);
        return in_array($id, $fieldIds);
    }

    protected static function fieldRepository(): FieldRepositoryInterface
    {
        return resolve(FieldRepositoryInterface::class);
    }
}
