<?php

namespace MetaFox\Group\Http\Requests\v1\Group;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Core\Rules\ImageRule;
use MetaFox\Storage\Rules\MaxFileUpload;

/**
 * Class UpdateAvatarRequest.
 */
class UpdateAvatarRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'image', new ImageRule(), new MaxFileUpload()],
        ];
    }
}
