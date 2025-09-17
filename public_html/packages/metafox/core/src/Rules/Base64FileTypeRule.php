<?php

namespace MetaFox\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64FileTypeRule implements Rule
{
    private string $fileType;

    public function __construct(string $fileType = 'photo')
    {
        $this->fileType = $fileType;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $file = upload()->convertBase64ToUploadedFile($value);

        if (!file_type()->verifyMime($file, $this->fileType)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('validation.mimes', [
            'attribute' => 'file',
            'values'    => file_type()->getMimeTypeFromType($this->fileType),
        ]);
    }
}
