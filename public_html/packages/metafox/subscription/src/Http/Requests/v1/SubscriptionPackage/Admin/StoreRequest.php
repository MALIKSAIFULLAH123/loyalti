<?php

namespace MetaFox\Subscription\Http\Requests\v1\SubscriptionPackage\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Rules\DowngradePackage;
use MetaFox\Subscription\Rules\VisibleRole;
use MetaFox\Subscription\Support\Helper;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Subscription\Http\Controllers\Api\v1\SubscriptionPackageAdminController::store
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
        $hasDisableFields = $this->hasDisabledFields();

        $data = $this->all();

        $resource = $this->resource;

        $rules = [
            'title'               => ['required', 'string', 'between:1,' . Helper::MAX_PACKAGE_TITLE_LENGTH],
            'text'                => ['required', 'string', 'between:1,' . Helper::MAX_PACKAGE_DESCRIPTION_LENGTH],
            'thumbnail'           => ['sometimes', 'array'],
            'thumbnail.temp_file' => ['required_with:thumbnail', 'numeric'],
            'thumbnail.status'    => ['sometimes', 'required_with:thumbnail', 'string'],
            'upgraded_role_id'    => [$hasDisableFields ? 'sometimes' : 'required', 'numeric', 'exists:auth_roles,id'],
            'is_on_registration'  => [new AllowInRule([0, 1])],
            'is_active'           => [new AllowInRule([0, 1])],
            'visible_roles'       => ['sometimes', 'nullable', new VisibleRole($resource)],
            'is_free'             => [new AllowInRule([0, 1])],
        ];

        $rules = array_merge($rules, $this->addPriceRules('price', 'required_if:is_free,0'));

        $rules = array_merge($rules, $this->getDependencyPackageRules());

        $rules = array_merge($rules, $this->getRecurringRules($hasDisableFields, $data));

        return $rules;
    }

    public function messages(): array
    {
        $dayNotificationMessage = __p('subscription::validation.days_notification_before_subscription_expired.numeric', [
            'number' => Helper::MIN_TOTAL_DAY_RECURRING_FOR_NOTIFICATION,
        ]);

        $upgradeRoleMessage = __p('subscription::validation.upgraded_role_id.required');

        $titleMessage = __p('core::validation.name.required');

        $descriptionMessage = __p('core::validation.description.required');

        $periodMessage = __p('subscription::validation.recurring_period.required');

        $renewMethodMessage = __p('subscription::validation.allowed_renew_type.required');

        $messages = [
            'title.required'                                            => $titleMessage,
            'title.string'                                              => $titleMessage,
            'title.between'                                             => __p('core::validation.name.length_between', [
                'min' => 1,
                'max' => MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH,
            ]),
            'text.required'                                             => $descriptionMessage,
            'text.string'                                               => $descriptionMessage,
            'upgraded_role_id.required'                                 => $upgradeRoleMessage,
            'upgraded_role_id.numeric'                                  => $upgradeRoleMessage,
            'upgraded_role_id.exists'                                   => $upgradeRoleMessage,
            'days_notification_before_subscription_expired.required_if' => $dayNotificationMessage,
            'days_notification_before_subscription_expired.numeric'     => $dayNotificationMessage,
            'days_notification_before_subscription_expired.between'     => __p('subscription::validation.days_notification_before_subscription_expired.between'),
            'recurring_period.required_if'                              => $periodMessage,
            'recurring_period.string'                                   => $periodMessage,
            'allowed_renew_type.required_if'                            => $renewMethodMessage,
            'allowed_renew_type.array'                                  => $renewMethodMessage,
            'upgraded_package_id.*.exists'                              => __p('subscription::validation.upgraded_package_must_be_existed'),
            'upgraded_package_id.*.numeric'                             => __p('subscription::validation.upgraded_package_must_be_existed'),
        ];

        $messages = array_merge($messages, $this->addPriceValidationMessages('price'));

        $messages = array_merge($messages, $this->addPriceValidationMessages('recurring_price', 0, false));

        return $messages;
    }

    protected function addPriceValidationMessages(string $name, int $min = 0, bool $allowEqualTo = true): array
    {
        $currencies = app('currency')->getActiveOptions();

        $messages = [];

        $minMessage = 'subscription::validation.price_must_be_greater_than_or_equal_to_number';

        if (!$allowEqualTo) {
            $minMessage = 'subscription::validation.price_must_be_greater_than_number';
        }

        $rules = [
            'required_if' => 'core::validation.numeric',
            'numeric'     => 'core::validation.numeric',
        ];

        switch ($allowEqualTo) {
            case true:
                $rules['min'] = $minMessage;
                break;
            default:
                $rules['gt'] = $minMessage;
                break;
        }

        foreach ($rules as $rule => $message) {
            foreach ($currencies as $currency) {
                $messages[$name . '.' . $currency['value'] . '.' . $rule] = __p($message, [
                    'attribute' => $currency['label'],
                    'number'    => $min,
                ]);
            }
        }

        return $messages;
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $hasDisableFields = $this->hasDisabledFields();

        $data = $this->handleBasic($data);

        $data = $this->handleRecurringFields($data, $hasDisableFields);
        if (!Arr::has($data, 'background_color_for_comparison')) {
            Arr::set($data, 'background_color_for_comparison', Helper::DEFAULT_BACKGROUND_COLOR);
        }

        $data = $this->handleStatus($data);

        if (Arr::get($data, 'is_free')) {
            Arr::set($data, 'price', $this->validatedFreePrice());
        }

        $data = $this->unsetVariables($data, $hasDisableFields);

        $data = $this->handleInitialPrices($data);

        return $this->handleRecurringPrices($data);
    }

    protected function handleInitialPrices(array $data): array
    {
        $prices = Arr::get($data, 'price');

        if (!is_array($prices)) {
            return $data;
        }

        Arr::set($data, 'price', $this->handlePrices($prices));

        return $data;
    }

    protected function handleRecurringPrices(array $data): array
    {
        $prices = Arr::get($data, 'recurring_price');

        if (!is_array($prices)) {
            return $data;
        }

        Arr::set($data, 'recurring_price', $this->handlePrices($prices));

        return $data;
    }

    protected function handlePrices(array $prices): array
    {
        foreach ($prices as $key => $price) {
            if (!is_numeric($price)) {
                continue;
            }

            $prices[$key] = round($price, 2);
        }

        return $prices;
    }

    protected function validatedFreePrice(): array
    {
        $currencies = app('currency')->getActiveOptions();

        $prices = [];

        foreach ($currencies as $currency) {
            Arr::set($prices, $currency['value'], 0);
        }

        return $prices;
    }

    protected function handleBasic(array $attributes): array
    {
        if (!Arr::has($attributes, 'thumbnail')) {
            Arr::set($attributes, 'thumbnail', []);
        }

        if (!Arr::has($attributes, 'is_on_registration')) {
            Arr::set($attributes, 'is_on_registration', 0);
        }

        if (!Arr::has($attributes, 'is_active')) {
            Arr::set($attributes, 'is_active', 1);
        }

        if (!Arr::has($attributes, 'is_free')) {
            Arr::set($attributes, 'is_free', 0);
        }

        $thumbnail = Arr::get($attributes, 'thumbnail');

        if (Arr::has($thumbnail, 'temp_file')) {
            Arr::set($attributes, 'temp_file', Arr::get($thumbnail, 'temp_file'));
        }

        if (Arr::has($thumbnail, 'status') && Arr::get($thumbnail, 'status') == 'remove') {
            Arr::set($attributes, 'remove_image', true);
        }

        if (!Arr::has($attributes, 'upgraded_package_id') || !is_array($attributes['upgraded_package_id'])) {
            Arr::set($attributes, 'upgraded_package_id', null);
        }

        if (!Arr::has($attributes, 'downgraded_package_id')) {
            Arr::set($attributes, 'downgraded_package_id', null);
        }

        if (!Arr::has($attributes, 'visible_roles') || !is_array($attributes['visible_roles'])) {
            Arr::set($attributes, 'visible_roles', []);
        }

        $visibleRoles = Arr::get($attributes, 'visible_roles');

        if (is_array($visibleRoles) && count($visibleRoles)) {
            $defaultRoles = resolve(SubscriptionPackageRepositoryInterface::class)->getRoleOptionsForVisibility();

            if (is_array($defaultRoles)) {
                $defaultRoles = Arr::pluck($defaultRoles, 'value');
            }

            $hasDefaultRoles = is_array($defaultRoles) && count($defaultRoles) > 0;

            switch ($hasDefaultRoles) {
                case true:
                    if (count(array_diff($defaultRoles, $visibleRoles)) == 0) {
                        Arr::set($attributes, 'visible_roles', null);
                    }
                    break;
                default:
                    Arr::set($attributes, 'visible_roles', []);
                    break;
            }
        }

        return $attributes;
    }

    protected function handleStatus(array $attributes): array
    {
        $isActive = (int)Arr::get($attributes, 'is_active');

        $status = match ($isActive) {
            1 => Helper::STATUS_ACTIVE,
            0 => Helper::STATUS_DEACTIVE,
        };

        Arr::set($attributes, 'status', $status);

        unset($attributes['is_active']);

        return $attributes;
    }

    protected function handleRecurringFields(array $attributes, bool $hasDisableFields): array
    {
        $isRecurring = Arr::get($attributes, 'is_recurring');

        if (!$hasDisableFields) {
            if (!Arr::has($attributes, 'is_recurring')) {
                Arr::set($attributes, 'is_recurring', 0);
            }

            if (!$isRecurring) {
                Arr::set($attributes, 'recurring_price', null);
            }

            if (!$isRecurring || !Arr::has($attributes, 'allowed_renew_type')) {
                Arr::set($attributes, 'allowed_renew_type', null);
            }

            if (!$isRecurring || !Arr::has($attributes, 'days_notification_before_subscription_expired')) {
                Arr::set($attributes, 'days_notification_before_subscription_expired', Helper::DEFAULT_DAY_RECURRING_FOR_NOTIFICATION);
            }

            if (!$isRecurring || !Arr::has($attributes, 'recurring_period')) {
                Arr::set($attributes, 'recurring_period', null);
            }

            unset($attributes['is_recurring']);
        }

        if ($isRecurring) {
            $daysNotification = Arr::get($attributes, 'days_notification_before_subscription_expired', Helper::DEFAULT_DAY_RECURRING_FOR_NOTIFICATION);

            Arr::set($attributes, 'days_notification_before_subscription_expired', (int)$daysNotification);
        }

        return $attributes;
    }

    protected function unsetVariables(array $attributes, bool $hasDisableFields): ?array
    {
        if ($hasDisableFields) {
            unset($attributes['upgraded_role_id']);
            unset($attributes['is_recurring']);
            unset($attributes['allowed_renew_type']);
            unset($attributes['days_notification_before_subscription_expired']);
            unset($attributes['recurring_period']);
        }

        return $attributes;
    }

    protected function addPriceRules(string $name, string $requiredIf, bool $hasDisabledFields = false, bool $allowEqualTo = true): array
    {
        $currencies       = app('currency')->getActiveOptions();
        $enablePriceField = $this->getEnablePriceField($currencies);
        $userCurrency     = app('currency')->getUserCurrencyId(user());
        $rule             = ['sometimes', 'numeric'];
        $rules            = [];

        foreach ($currencies as $currency) {
            $currencyRule = $rule;

            if ($hasDisabledFields && !in_array($currency['value'], $enablePriceField)) {
                continue;
            } else {
                $currencyRule[] = 'nullable';
            }

            if ($userCurrency == $currency['value']) {
                $currencyRule = array_merge($currencyRule, [$requiredIf]);
            }

            $currencyRule = array_merge($currencyRule, [$allowEqualTo ? 'min:0' : 'gt:0.01']);

            $rules[$name . '.' . $currency['value']] = $currencyRule;
        }

        return $rules;
    }

    protected function getEnablePriceField(array $currencies): array
    {
        return [];
    }

    protected function getRecurringRules(bool $hasDisableFields, array $data): array
    {
        $periodDays = Helper::getRecurringPeriodDays(Arr::get($data, 'recurring_period'));

        if ($periodDays > 0) {
            $periodDays -= 1;
        }

        $rules = [
            'is_recurring'                                  => [new AllowInRule([0, 1])],
            'allowed_renew_type'                            => ['nullable', 'required_if:is_recurring,1', 'array'],
            'days_notification_before_subscription_expired' => ['nullable', 'required_if:is_recurring,1', 'numeric', 'between:' . Helper::MIN_TOTAL_DAY_RECURRING_FOR_NOTIFICATION . ',' . $periodDays],
            'recurring_period'                              => ['nullable', 'required_if:is_recurring,1', 'string', new AllowInRule(Helper::getRecurringPeriodType())],
        ];

        if ($hasDisableFields) {
            foreach ($rules as $key => $rule) {
                $rules[$key] = array_merge(['sometimes'], $rule);
            }
        }

        $rules = array_merge($rules, $this->addPriceRules('recurring_price', 'required_if:is_recurring,1', $hasDisableFields, false));

        return $rules;
    }

    protected function getDependencyPackageRules(): array
    {
        return [
            'upgraded_package_id'   => ['sometimes', 'nullable', 'array'],
            'upgraded_package_id.*' => ['numeric', 'exists:subscription_packages,id'],
            'downgraded_package_id' => ['sometimes', 'nullable', new DowngradePackage()],
        ];
    }

    protected function hasDisabledFields(): bool
    {
        return false;
    }
}
