<?php

namespace MetaFox\Music\Http\Requests\v1\Song;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Rules\Mp3FileRule;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Rules\ValidImageRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Music\Http\Controllers\Api\v1\SongController::update;
 * stub: api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
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
            'name'        => ['required', 'string', new ResourceNameRule('music.music_song')],
            'description' => ['sometimes', 'string', 'nullable'],
            'file'        => [resolve(Mp3FileRule::class)],
            'genres'      => ['required', 'array'],
            'genres.*'    => ['required_with:genres', 'numeric', new ExistIfGreaterThanZero('exists:music_genres,id')],
            'owner_id'    => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];

        $rules = $this->applyAttachmentRules($rules);

        $this->handleHideRule($rules);

        return $rules;
    }

    private function checkBelongToAlbum(): bool
    {
        $id = (int) $this->route('song');

        $song = Song::query()->findOrFail($id);

        if (!$song->album_id) {
            return false;
        }

        return true;
    }

    private function handleHideRule(array &$rules): void
    {
        if ($this->checkBelongToAlbum()) {
            return;
        }

        $rules['thumbnail'] = ['sometimes', resolve(ValidImageRule::class)];
        $rules['privacy']   = ['required', new PrivacyRule([
            'validate_privacy_list' => false,
        ])];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!$this->checkBelongToAlbum()) {
            $data = $this->handlePrivacy($data);
        }

        if (!array_key_exists('genres', $data)) {
            $data['genres'] = [Settings::get('music.music_song.song_default_genre')];
        }

        $data['thumb_temp_file']  = Arr::get($data, 'thumbnail.temp_file', 0);
        $data['remove_thumbnail'] = Arr::get($data, 'thumbnail.status', false);

        $data['temp_file'] = Arr::get($data, 'file.temp_file', 0);

        return $data;
    }
}
