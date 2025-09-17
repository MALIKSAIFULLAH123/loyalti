<?php

namespace MetaFox\User\Http\Requests\v1\Account;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class SettingRequest.
 */
class SettingRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $context = user();
        $id      = $this->route('id') ?? $context->entityId();

        $this->merge([
            'id' => $id,
        ]);
    }

    protected function passedValidation()
    {
        $context = user();
        $user    = UserEntity::getById(parent::validated('id'))->detail;

        policy_authorize(UserPolicy::class, 'update', $context, $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id' => ['required', 'numeric', 'exists:user_entities,id'],
        ];
    }
}
