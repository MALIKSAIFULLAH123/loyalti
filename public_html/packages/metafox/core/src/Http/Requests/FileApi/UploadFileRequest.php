<?php

namespace MetaFox\Core\Http\Requests\FileApi;

use Illuminate\Support\Arr;
use MetaFox\Core\Http\Requests\BaseFileRequest;
use MetaFox\Core\Support\FileSystem\Image\Plugins\ResizeImage;
use MetaFox\Platform\MetaFoxFileType;
use MetaFox\Storage\Rules\MaxFileUpload;

/**
 * Class UploadFileRequest.
 */
class UploadFileRequest extends BaseFileRequest
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
        return array_merge($this->getStorageIdRule(), [
            'file'              => ['required', new MaxFileUpload()],
            'file_type'         => ['sometimes', 'string', 'nullable'],
            'item_type'         => ['sometimes', 'string'],
            'thumbnail_sizes'   => ['sometimes', 'array'],
            'thumbnail_sizes.*' => ['string'],
            'base64'            => ['sometimes', 'string'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = Arr::add($data, 'item_type', 'photo');
        $data = Arr::add($data, 'thumbnail_sizes', ResizeImage::SIZE);
        $data = Arr::add($data, 'file_type', MetaFoxFileType::PHOTO_TYPE);

        $fileType = Arr::get($data, 'file_type');
        $data     = Arr::add($data, 'storage_id', $fileType);

        //transform file_type in case client sent a mime-type instead of a actually types: photo, video or audio.
        $data['file_type'] = file_type()->transformFileType($fileType);

        return $data;
    }
}
