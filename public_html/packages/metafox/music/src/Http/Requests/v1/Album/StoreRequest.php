<?php

namespace MetaFox\Music\Http\Requests\v1\Album;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Music\Rules\MaxUploadSongAlbumRule;
use MetaFox\Music\Rules\Mp3FileRule;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Rules\ValidImageRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Music\Http\Controllers\Api\v1\AlbumController::store;
 * stub: api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name'      => ['required', 'string', new ResourceNameRule('music.music_album')],
            'text'      => ['sometimes', 'nullable', 'string', new ResourceTextRule(true)],
            'year'      => ['required', 'digits:4', 'integer', 'min:1900', 'max:' . (int) Carbon::now()->addYear()->format('Y')],
            'thumbnail' => ['sometimes', resolve(ValidImageRule::class)],
            'genres'    => ['required', 'array'],
            'genres.*'  => ['required_with:genres', 'numeric', new ExistIfGreaterThanZero('exists:music_genres,id')],
            'owner_id'  => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'privacy'   => ['required', new PrivacyRule()],
        ];

        $rules = $this->applyAttachmentRules($rules);

        $this->handleSongRule($rules);

        return $rules;
    }

    protected function handleSongRule(array &$rules): void
    {
        $context          = user();
        $maxSongPerUpload = $context->getPermissionValue('music_song.maximum_number_of_songs_per_upload');
        $rule             = [
            'songs'               => ['required', 'array', new MaxUploadSongAlbumRule($maxSongPerUpload)],
            'songs.*'             => ['required_with:songs', resolve(Mp3FileRule::class)],
            'songs.*.name'        => ['required_with:songs', 'string', new ResourceNameRule('music.music_song')],
            'songs.*.description' => ['sometimes', 'nullable', 'string'],
        ];

        $rules = array_merge($rules, $rule);
    }

    /**
     * @throws ValidationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data['temp_file']       = Arr::get($data, 'file.temp_file', 0);
        $data['thumb_temp_file'] = Arr::get($data, 'thumbnail.temp_file', 0);

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        if (!array_key_exists('genres', $data)) {
            $data['genres'] = [Settings::get('music.music_song.song_default_genre')];
        }

        $this->handleSongAttribute($data);

        return $data;
    }

    protected function handleSongAttribute(array &$data): void
    {
        $genres  = Arr::get($data, 'genres', []);
        $privacy = Arr::get($data, 'privacy');

        $songs = [];

        foreach (Arr::get($data, 'songs', []) as $song) {
            $song['genres']  = $genres;
            $song['privacy'] = $privacy;

            $songs[] = $song;
        }

        Arr::set($data, 'songs', $songs);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => __p('music::validation.file_is_a_required_field'),
        ];
    }
}
