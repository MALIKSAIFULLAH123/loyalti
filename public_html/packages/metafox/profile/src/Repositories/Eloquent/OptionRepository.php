<?php

namespace MetaFox\Profile\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Option;
use MetaFox\Profile\Repositories\OptionRepositoryInterface;
use MetaFox\Profile\Repositories\ValueRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * class OptionRepository.
 */
class OptionRepository extends AbstractRepository implements OptionRepositoryInterface
{
    public function model()
    {
        return Option::class;
    }

    protected function valueRepository(): ValueRepositoryInterface
    {
        return resolve(ValueRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function createOptions(Field $field, array $attributes): void
    {
        foreach ($attributes as $ordering => $attribute) {
            $label = Arr::get($attribute, 'label');

            /** @var Option $model */
            $model = $this->getModel()->newInstance();

            $model->fill([
                'label'    => $label,
                'field_id' => $field->entityId(),
                'ordering' => Arr::get($attribute, 'ordering', $ordering),
            ]);

            $model->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function updateOptions(Field $field, array $attributes): void
    {
        foreach ($attributes as $ordering => $attribute) {
            $optionId = Arr::get($attribute, 'id');

            /** @var Option $model */
            $model = $this->find($optionId);
            $model->fill([
                'label'    => Arr::get($attribute, 'label'),
                'ordering' => Arr::get($attribute, 'ordering', $ordering),
            ]);

            $model->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function removeOptions(Field $field, array $attributes): void
    {
        $removeIds = collect($attributes)->pluck('id')->toArray();
        $field->options()->whereIn('id', $removeIds)->delete();
        $this->valueRepository()->deleteValue($field, $removeIds);
    }

    public function getAllOptions(): mixed
    {
        return Cache::rememberForever(__METHOD__ . app()->getLocale(), function () {
            return $this->getModel()->newQuery()
                ->orderBy('ordering')
                ->orderBy('id')
                ->get()
                ->groupBy('field_id')
                ->toArray();
        });
    }
}
