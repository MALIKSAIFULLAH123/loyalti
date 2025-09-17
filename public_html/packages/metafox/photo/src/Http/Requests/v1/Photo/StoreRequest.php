<?php

namespace MetaFox\Photo\Http\Requests\v1\Photo;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Photo\Rules\MaximumMediaPerUpload;
use MetaFox\Photo\Support\Traits\PhotoExtraInfo;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use PhotoExtraInfo;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $context      = user();
        $maxPerUpload = $context->getPermissionValue('photo.maximum_number_of_media_per_upload');

        $rules = [
            'categories'            => ['sometimes', 'array'],
            'categories.*'          => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class))],
            'album'                 => [
                'sometimes', 'integer', 'min:1', 'nullable', new ExistIfGreaterThanZero('exists:photo_albums,id'),
            ],
            'owner_id'              => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'tags'                  => ['sometimes', 'array'],
            'tags.*'                => ['string'],
            'files'                 => ['required', 'array', new MaximumMediaPerUpload((int) $maxPerUpload)],
            'files.*.id'            => ['required_with:files', 'numeric'],
            'files.*.type'          => ['required_with:files', 'string'],
            'files.*.status'        => ['string', new AllowInRule([MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_REMOVE_STATUS])],
            'thumbnail_sizes'       => ['sometimes', 'array'],
            'thumbnail_sizes.*'     => ['string'],
            'privacy'               => ['required', new PrivacyRule()],
            'location'              => [
                'sometimes', 'array',
            ],
            //todo location rule should be re-use able.
            'location.address'      => 'string',
            'location.lat'          => 'numeric',
            'location.lng'          => 'numeric',

            //New album
            'add_new_album'         => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'new_album_name'        => ['required_if:add_new_album,1', 'nullable', 'string', 'max:255'],
            'new_album_description' => ['sometimes', 'nullable', 'string'],
            'new_album_privacy'     => ['required_if:add_new_album,1', new PrivacyRule()],
        ];

        return $this->addExtraPhotoInfoRule($rules, 'files');
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // Handle parsing privacy data for new photos
        $data = $this->handlePrivacy($data);

        $data = $this->translateNewAlbum($data);

        // Handle parsing privacy data for new album
        if (array_key_exists('new_album', $data)) {
            $data['new_album'] = $this->handlePrivacy($data['new_album']);
        }

        if (!empty($data['location'])) {
            $data['location_name']      = $data['location']['address'];
            $data['location_latitude']  = $data['location']['lat'];
            $data['location_longitude'] = $data['location']['lng'];
            $data['location_address'] = $data['location']['full_address'];
            unset($data['location']);
        }

        $data['album_id'] = 0;
        if (isset($data['album'])) {
            $data['album_id'] = $data['album'];
        }

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        if (!array_key_exists('add_new_album', $data)) {
            $data['add_new_album'] = 0;
        }

        return $this->transformExtraPhotoInfo($data, 'files');
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'files.required'                => __p('photo::validation.media_files_are_required'),
            'new_album.name.required_if'    => __p('photo::validation.album_name_is_a_required_field'),
            'new_album.privacy.required_if' => __p('photo::validation.album_privacy_is_a_required_field'),
        ];
    }

    protected function translateNewAlbum(array $data): array
    {
        if (!array_key_exists('new_album_name', $data)) {
            return $data;
        }

        $data['new_album']['text']    = array_key_exists('new_album_description', $data) ? $data['new_album_description'] : '';
        $data['new_album']['name']    = $data['new_album_name'];
        $data['new_album']['privacy'] = $data['new_album_privacy'];

        unset($data['new_album_name'], $data['new_album_description'], $data['new_album_privacy']);

        return $data;
    }
}
