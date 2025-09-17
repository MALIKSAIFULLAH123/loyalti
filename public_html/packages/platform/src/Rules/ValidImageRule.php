<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxFileType;

class ValidImageRule implements Rule
{
    public function __construct(protected bool $isRequired = false)
    {
    }

    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        $id = (int) Arr::get($value, 'id');

        /*
         * It means existed file
         */
        if ($id > 0) {
            return true;
        }

        $fileId = Arr::get($value, 'temp_file');

        if (!$fileId) {
            return !$this->isRequired();
        }

        $ext = app('storage')->getExt($fileId);

        if (null === $ext) {
            return false;
        }

        if (!in_array($ext, MetaFoxFileType::PHOTO_EXTENSIONS)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __p('core::validation.file_must_be_image');
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }
}
