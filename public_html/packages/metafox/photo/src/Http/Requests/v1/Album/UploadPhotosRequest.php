<?php

namespace MetaFox\Photo\Http\Requests\v1\Album;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Photo\Rules\UploadedAlbumItems;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;

/**
 * Class UpdateRequest.
 */
class UploadPhotosRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => ['sometimes', 'string', 'nullable'],
            'id'   => ['required', 'numeric', 'exists:photo_albums,id'],
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'album', Arr::get($data, 'id'));

        return $data;
    }
}
