<?php

namespace MetaFox\Profile\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Value;
use MetaFox\Profile\Repositories\OptionRepositoryInterface;
use MetaFox\Profile\Repositories\ValueRepositoryInterface;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * class ValueRepository.
 */
class ValueRepository extends AbstractRepository implements ValueRepositoryInterface
{
    public function model()
    {
        return Value::class;
    }

    protected function optionsRepository(): OptionRepositoryInterface
    {
        return resolve(OptionRepositoryInterface::class);
    }

    public function deleteValue(Field $field, array $value): void
    {
        $query = $this->getModel()->newQuery()->where('field_id', $field->entityId());

        if ($field->edit_type == CustomField::MULTI_CHOICE) {
            $query->delete();

            return;
        }

        $query->whereIn('field_value_text', $value)->delete();
    }

    public function getValuesByFieldIds(User $user, array $fieldIds): Collection
    {
        $tableValue = $this->getModel()->getTable();
        $tableField = (new Field())->getTable();

        /**
         * @todo can be optimized by preparing all user field values in collection and process later
         */
        return $this->getModel()->newQuery()
            ->select("$tableValue.*")
            ->with('field')
            ->where('user_id', $user->entityId())
            ->whereIn('field_id', $fieldIds)
            ->leftJoin("$tableField", "$tableField.id", '=', "$tableValue.field_id")
            ->orderBy("$tableField.ordering")
            ->get();
    }


    public function handleFieldValue(Field $field, Value $value, array $attributes): mixed
    {
        $forForm    = Arr::get($attributes, 'for_form', false);
        $fieldValue = $value?->field_value_text;

        if ($fieldValue === null) {
            return null;
        }

        $options = $this->optionsRepository()->getAllOptions();
        $options = Arr::get($options, $field->entityId(), collect());
        $options = collect($options)->pluck('label', 'id')->toArray();

        return match ($forForm) {
            true  => CustomFieldFacade::transformValueForForm($field->edit_type, $fieldValue),
            false => CustomFieldFacade::transformValueForSection($field->edit_type, $fieldValue, $options)
        };
    }

    /**
     * @inheritDoc
     */
    public function getValuesByUser(User $user): Collection
    {
        return $this->getModel()->newQuery()
            ->where('user_id', '=', $user->entityId())
            ->get(['field_id', 'field_value_text', 'ordering']);
    }

    /**
     * @inheritDoc
     */
    public function createValue(User $user, array $attributes): Value
    {
        $fieldId = Arr::get($attributes, 'field_id');
        $value   = Arr::get($attributes, 'field_value');

        /**@var $model Value */
        $model = $this->getModel()->newModelQuery()->firstOrNew([
            'user_id'   => $user->id,
            'user_type' => $user->entityType(),
            'field_id'  => $fieldId,
        ], [
            'field_value_text' => $value,
        ]);

        return $model;
    }
}
