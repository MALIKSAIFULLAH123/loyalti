<?php
namespace MetaFox\Forum\Http\Requests\v1\Forum\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;

class SearchModeratorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string'],
            'forum_id' => ['required', 'integer', 'exists:forums,id'],
            'excluded_ids' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handleModeratorIds($data);

        return $this->handleSearch($data);
    }

    protected function handleSearch(array $data): array
    {
        if (!Arr::has($data, 'q')) {
            return $data;
        }

        $q = Arr::get($data, 'q');

        if (!is_string($q)) {
            Arr::forget($data, ['q']);
            return $data;
        }

        $q = trim($q);

        if (MetaFoxConstant::EMPTY_STRING === $q) {
            Arr::forget($data, ['q']);
            return $data;
        }

        Arr::set($data, 'q', $q);

        return $data;
    }

    protected function handleModeratorIds(array $data): array
    {
        if (!Arr::has($data, 'excluded_ids')) {
            return $data;
        }

        $excludedIds = Arr::get($data, 'excluded_ids');

        if (!is_string($excludedIds)) {
            Arr::forget($data, 'excluded_ids');
            return $data;
        }

        $excludedIds = explode(',', $excludedIds);

        if (!count($excludedIds)) {
            Arr::forget($data, 'excluded_ids');
            return $data;
        }

        Arr::set($data, 'excluded_ids', $excludedIds);

        return $data;
    }
}
