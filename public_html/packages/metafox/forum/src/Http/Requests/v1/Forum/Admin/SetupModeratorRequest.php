<?php

namespace MetaFox\Forum\Http\Requests\v1\Forum\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Forum\Repositories\ModeratorRepositoryInterface;

class SetupModeratorRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'moderator_ids' => ['sometimes', 'nullable', 'array'],
            'moderator_ids.*.id' => ['sometimes', 'numeric', 'exists:users,id'],
        ];

        $permissions = resolve(ModeratorRepositoryInterface::class)->getPerms();

        foreach ($permissions as $permission) {
            $rules[$permission['var_name']] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handleModerators($data);

        return $this->handleConfigs($data);
    }

    protected function handleConfigs(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($key === 'moderator_ids') {
                continue;
            }

            if (1 == $value) {
                continue;
            }

            Arr::forget($data, $key);
        }

        return $data;
    }

    protected function handleModerators(array $data): array
    {
        if (!Arr::has($data, 'moderator_ids')) {
            return $data;
        }

        $moderators = Arr::get($data, 'moderator_ids');

        if (!is_array($moderators)) {
            Arr::forget($data, 'moderator_ids');
            return $data;
        }

        $moderatorIds = array_column($moderators, 'id');

        Arr::set($data, 'moderator_ids', $moderatorIds);

        return $data;
    }
}
