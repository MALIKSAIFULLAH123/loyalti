<?php

namespace MetaFox\Storage\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class MaxFileUpload implements RuleContract
{
    private float $maxUploadSize = 0;

    /**
     * @inheritDoc
     * @throws \Illuminate\Validation\ValidationException
     */
    public function passes($attribute, $value): bool
    {
        if (!$value instanceof UploadedFile) {
            return false;
        }

        $maxUpload = $this->getMaxUploadSize($value);

        // Unlimited case
        if ($maxUpload == 0) {
            return true;
        }

        $validator = Validator::make(
            [$attribute => $value],
            [$attribute => ['max:' . $maxUpload]],
        );

        return $validator->passes();
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return __p('storage::validation.uploaded_file_exceed_limit', [
            'limit' => human_readable_bytes($this->maxUploadSize * 1024),
        ]);
    }

    public function getMaxUploadSize(UploadedFile $file): float
    {
        $fileType            = file_type()->getTypeByMime($file->getMimeType());
        $this->maxUploadSize = round(file_type()->getFilesizePerType($fileType ?? '') / 1024, 5);

        return $this->maxUploadSize;
    }
}
