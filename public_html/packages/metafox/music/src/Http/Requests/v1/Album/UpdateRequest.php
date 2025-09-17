<?php

namespace MetaFox\Music\Http\Requests\v1\Album;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
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
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Music\Http\Controllers\Api\v1\AlbumController::update;
 * stub: api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends StoreRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
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
            'privacy'   => ['required', new PrivacyRule([
                'validate_privacy_list' => false,
            ])],
        ];

        $rules = $this->applyAttachmentRules($rules);

        $this->handleSongRule($rules);

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        if (!array_key_exists('genres', $data)) {
            $data['genres'] = [Settings::get('music.music_song.song_default_genre')];
        }

        $this->handleSongAttribute($data);

        if (array_key_exists('songs', $data)) {
            $data['songs'] = $this->handleAlbumItems($data['songs']);
        }

        $data['thumb_temp_file']  = Arr::get($data, 'thumbnail.temp_file', 0);
        $data['remove_thumbnail'] = Arr::get($data, 'thumbnail.status', false);

        return $data;
    }

    protected function handleSongAttribute(array &$data): void
    {
        $genres    = Arr::get($data, 'genres', []);
        $privacy   = Arr::get($data, 'privacy');
        $fileSongs = Arr::get($data, 'songs', []);
        $songs     = [];

        foreach ($fileSongs as $song) {
            if (!Arr::has($song, 'status')) {
                Arr::set($song, 'status', MetaFoxConstant::FILE_UPDATE_STATUS);
            }

            /*
             * No update genres with existed songs
             */
            $song['genres'] = null;

            /*
             * Only add album genres with new songs
             */
            if (MetaFoxConstant::FILE_NEW_STATUS == Arr::get($song, 'status')) {
                $song['genres'] = $genres;
            }

            $song['privacy'] = $privacy;

            if (MetaFoxPrivacy::CUSTOM == $song['privacy']) {
                Arr::set($song, 'list', Arr::get($data, 'list', []));
            }

            $songs[] = $song;
        }

        if (!count($songs)) {
            unset($data['songs']);

            return;
        }

        Arr::set($data, 'songs', $songs);
    }

    protected function handleAlbumItems(array $items): array
    {
        return collect($items)
            ->values()
            ->groupBy('status')
            ->toArray();
    }
}
