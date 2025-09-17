<?php

namespace MetaFox\Page\Http\Requests\v1\Page;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Core\Rules\ImageRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Storage\Rules\MaxFileUpload;

/**
 * Class UpdateCoverRequest.
 */
class UpdateCoverRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image'     => ['sometimes', new ImageRule(), new MaxFileUpload()],
            'temp_file' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'position'  => ['sometimes', 'string'],
        ];
    }
}
