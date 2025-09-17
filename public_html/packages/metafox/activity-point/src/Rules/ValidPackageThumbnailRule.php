<?php

namespace MetaFox\ActivityPoint\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxFileType;

class ValidPackageThumbnailRule implements Rule
{
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
            return true;
        }

        $mime = app('storage')->getMimeType($fileId);

        if (null === $mime) {
            return false;
        }

        return file_type()->verifyMimeTypeByType($mime, MetaFoxFileType::PHOTO_TYPE);
    }

    public function message()
    {
        return __p('activitypoint::validation.thumbnail_must_be_image');
    }
}
