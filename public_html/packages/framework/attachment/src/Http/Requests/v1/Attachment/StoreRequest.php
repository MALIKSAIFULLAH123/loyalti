<?php

namespace MetaFox\Attachment\Http\Requests\v1\Attachment;

use MetaFox\Attachment\Rules\AttachmentFileTypeRule;
use MetaFox\Core\Http\Requests\BaseFileRequest;
use MetaFox\Platform\Facades\Settings;

class StoreRequest extends BaseFileRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $fileSize = Settings::get('core.attachment.maximum_file_size_each_attachment_can_be_uploaded'); //in bytes

        $fileRule = [
            'required',
            'file',
            new AttachmentFileTypeRule(),
        ];

        if (is_int($fileSize) && $fileSize > 0) {
            $fileRule[] = sprintf('max:%d', round($fileSize / 1024, 0));
        }

        return array_merge($this->getStorageIdRule(), [
            'file'        => $fileRule,
            'item_type'   => ['required', 'string'],
            'upload_type' => 'sometimes|string|nullable',
        ]);
    }
}
