<?php

namespace MetaFox\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class ImageRule implements Rule
{
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
        if (!$value instanceof UploadedFile) {
            return false;
        }

        $data  = ['file' => $value];
        $rules = [
            'file' => ['mimetypes:' . file_type()->getMimeTypeFromType('photo')],
        ];

        $validator = Validator::make($data, $rules);

        return $validator->passes();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('validation.file_must_be_an_image_of_types', [
            'mime_types' => file_type()->getMimeTypeFromType('photo'),
        ]);
    }
}
