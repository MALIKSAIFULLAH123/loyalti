<?php

namespace MetaFox\User\Http\Requests\v1\Account;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class ItemPrivacySettingsRequest.
 */
class ItemPrivacySettingsRequest extends FormRequest
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
        $id      = (int) parent::validated('id');

        if ($context->entityId() != $id) {
            policy_authorize(UserPolicy::class, 'view', $context, UserEntity::getById($id)->detail);
        }
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
