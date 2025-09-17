<?php

namespace MetaFox\Poll\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

class UpdateBannerRule implements RuleContract
{
    /**
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $validator = $this->getValidator($value);

        return $validator->passes();
    }

    /**
     * @param  mixed     $value
     * @return Validator
     */
    public function getValidator($value): Validator
    {
        if ($this->isImageRequired()) {
            return ValidatorFacade::make(['file' => $value], [
                'file.temp_file' => ['required', 'numeric', 'exists:storage_files,id'],
                'file.status'    => ['required', 'string', new AllowInRule(['keep', 'update', 'remove'])],
            ]);
        }

        return ValidatorFacade::make(['file' => $value], [
            'file.temp_file' => ['required_if:file.status,update', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'file.status'    => ['required', 'string', new AllowInRule(['keep', 'update', 'remove'])],
        ]);
    }

    public function isImageRequired(): bool
    {
        return Settings::get('poll.is_image_required', false);
    }

    public function message(): string
    {
        return __p('validation.field_is_a_required_field', ['field' => 'Banner']);
    }
}
