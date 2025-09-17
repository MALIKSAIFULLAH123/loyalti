<?php

namespace MetaFox\Music\Http\Requests\v1\Playlist;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * Class AddSongRequest.
 */
class AddSongRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'item_id'        => ['required', 'numeric', 'exists:music_songs,id'],
            'playlist_ids'   => ['nullable', 'array'],
            'playlist_ids.*' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:music_playlists,id')],
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required'      => __p('validation.required'),
            'playlist_ids.required' => __p('validation.required'),
        ];
    }
}
