<?php

namespace MetaFox\Advertise\Http\Requests\v1\Placement\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ResourceTextRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\PlacementAdminController::store
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
    public function rules()
    {
        $typeOptions = array_column(Support::getPlacementTypes(), 'value');

        $rules = [
            'title'                => ['required', 'string', 'max:' . MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH],
            'text'                 => ['required', 'string', new ResourceTextRule()],
            'placement_type'       => ['required', new AllowInRule($typeOptions)],
            'allowed_user_roles'   => ['nullable', 'array'],
            'allowed_user_roles.*' => ['required_with:allowed_user_roles', 'numeric', 'exists:auth_roles,id'],
            'is_active'            => ['required', new AllowInRule([0, 1])],
            'price'                => ['array'],
        ];

        return $this->addPriceRules($rules);
    }

    protected function addPriceRules(array $rules): array
    {
        $currencies   = app('currency')->getActiveOptions();

        $defaultCurrency = app('currency')->getDefaultCurrencyId();

        $name = 'price';

        foreach ($currencies as $currency) {
            $rule = ['sometimes', 'numeric', 'nullable', 'min:0'];

            if ($defaultCurrency == $currency['value']) {
                $rule = ['required', 'numeric', 'min:0'];
            }

            $rules[$name . '.' . $currency['value']] = $rule;
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->prepareRoles($data);

        return $this->handlePrices($data);
    }

    protected function handlePrices(array $data): array
    {
        $prices = Arr::get($data, 'price');

        if (!is_array($prices) || !count($prices)) {
            return $data;
        }

        foreach ($prices as $key => $price) {
            if (!is_numeric($price)) {
                continue;
            }

            $prices[$key] = round($price, 2);
        }

        return array_merge($data, [
            'price' => $prices,
        ]);
    }

    protected function prepareRoles(array $data): array
    {
        $roleIds = Arr::get($data, 'allowed_user_roles');

        if (!is_array($roleIds) || !count($roleIds)) {
            Arr::set($data, 'allowed_user_roles', null);

            return $data;
        }

        $repository = resolve(RoleRepositoryInterface::class);

        $roles = $repository->getRoleOptions();

        $availableRoleIds = array_column($roles, 'value');

        $disallowedRoleIds = Support::getDisallowedUserRoleOptions();

        $availableRoleIds = array_filter($availableRoleIds, function ($availableRoleId) use ($disallowedRoleIds) {
            return !in_array($availableRoleId, $disallowedRoleIds);
        });

        $diff = array_diff($availableRoleIds, $roleIds);

        if (count($diff)) {
            return $data;
        }

        /*
         * Null means all available roles
         */
        Arr::set($data, 'allowed_user_roles', null);

        return $data;
    }
}
