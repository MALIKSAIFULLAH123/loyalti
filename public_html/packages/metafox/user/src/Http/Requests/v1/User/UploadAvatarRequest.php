<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Core\Rules\ImageRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Storage\Rules\MaxFileUpload;

/**
 * Class UploadAvatarRequest.
 */
class UploadAvatarRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image_crop' => ['required', 'string'],
            'image'      => ['sometimes', new ImageRule(), new MaxFileUpload()],
            'temp_file'  => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'photo_id'   => ['sometimes', 'numeric', 'nullable', new ExistIfGreaterThanZero('exists:photos,id')],
        ];
    }
}
