<?php

namespace MetaFox\Marketplace\Http\Requests\v1\Listing;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\PrivacyRule;

class UpdateRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['categories.*'] = ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class), $this->route('marketplace'))];
        $rules['privacy']      = ['sometimes', new PrivacyRule([
            'validate_privacy_list' => false,
        ])];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }

    /**
     * @throws AuthenticationException
     */
    protected function applyAllowPaymentRules($rules): array
    {
        if (!$this->canAllowPayment()) {
            return $rules;
        }

        return parent::applyAllowPaymentRules($rules);
    }

    /**
     * @throws AuthenticationException
     */
    protected function applyAllowPointPaymentRules($rules): array
    {
        if (!$this->canAllowPointPayment() || !$this->canAllowPayment()) {
            return $rules;
        }

        return parent::applyAllowPointPaymentRules($rules);
    }

    /**
     * @throws AuthenticationException
     */
    protected function validatedAllowPayment($data): array
    {
        if (!$this->canAllowPayment()) {
            unset($data['allow_payment']);
            unset($data['allow_point_payment']);

            return $data;
        }

        if (!$this->canAllowPointPayment()) {
            unset($data['allow_point_payment']);
        }

        return $data;
    }
}
