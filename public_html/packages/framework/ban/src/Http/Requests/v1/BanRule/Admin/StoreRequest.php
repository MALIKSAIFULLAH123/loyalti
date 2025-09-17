<?php

namespace MetaFox\Ban\Http\Requests\v1\BanRule\Admin;

use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;
use MetaFox\Ban\Facades\Ban;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Ban\Http\Controllers\Api\v1\BanRuleAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->getCommonRules(), Ban::getValidationRules($this->getType()));
    }

    protected function getCommonRules(): array
    {
        $rules = [
            'type' => ['required', new AllowInRule(Ban::getAllowedBanRuleTypes())],
        ];

        if (!Ban::isSupportBanUser($this->getType())) {
            return $rules;
        }

        $rules['is_ban_user'] = ['sometimes', 'numeric', new AllowInRule([0, 1])];

        if (!$this->request->get('is_ban_user')) {
            return $rules;
        }

        return array_merge($rules, [
            'day'                 => ['required', 'numeric', 'min:0'],
            'reason'              => ['sometimes', 'nullable'],
            'return_user_group'   => ['required', 'numeric', 'min:1', 'exists:auth_roles,id'],
            'user_group_effected' => ['nullable', 'array'],
        ]);
    }

    protected function getType(): string
    {
        $type = $this->request->get('type');

        if (empty($type)) {
            throw new InvalidArgumentException(__p('ban::validation.type_is_invalid'));
        }

        return $type;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        Ban::getValidatedRules($this->getType(), $data);

        return $data;
    }
}
