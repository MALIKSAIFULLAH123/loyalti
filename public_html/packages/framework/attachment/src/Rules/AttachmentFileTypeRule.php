<?php

namespace MetaFox\Attachment\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use MetaFox\Core\Support\Facades\AttachmentFileType;

class AttachmentFileTypeRule implements RuleContract
{
    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        if (!$value instanceof UploadedFile) {
            return false;
        }

        $mimeTypes    = AttachmentFileType::getAllMineTypeActive();
        $fileRule     = !empty($mimeTypes) ? 'mimetypes:' . implode(',', $mimeTypes) : 'prohibited';

        $validator = Validator::make([
            'file' => $value,
        ], ['file' => $fileRule]);

        if (!$validator->passes()) {
            return false;
        }

        $allowExtensions = AttachmentFileType::getAllExtensionActive();
        $fileExtensions  = $value->clientExtension();
        if (!in_array($fileExtensions, $allowExtensions)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return __p('validation.cannot_play_back_the_file_the_format_is_not_supported');
    }
}
