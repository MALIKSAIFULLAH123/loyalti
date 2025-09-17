<?php

namespace MetaFox\Activity\Http\Resources\v1\Type\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Activity\Models\Type as Model;

/**
 * Class TypeItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TypeItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $data = $this->getData();

        $values = $this->getValues();

        $data = array_merge($data, $values);

        $package = Arr::get($data, 'package');

        if (!$package) {
            $package = [
                'title' => Arr::get($data, 'module_id'),
            ];
        }

        return array_merge($data, [
            'title'   => __p($data['title']),
            'package' => $package,
        ]);
    }

    protected function getData(): array
    {
        $this->resource->loadMissing(['package']);

        $data = $this->resource->toArray();

        unset($data['value_actual']);

        unset($data['value_default']);

        return $data;
    }

    protected function getValues(): array
    {
        $values = $this->resource->value_actual ?? $this->resource->value_default;

        if (!is_array($values)) {
            return [];
        }

        if (!count($values)) {
            return [];
        }

        return Arr::map($values, function ($value) {
            return (bool) $value;
        });
    }
}
