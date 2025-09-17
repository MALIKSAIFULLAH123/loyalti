<?php

namespace MetaFox\Photo\Http\Requests\v1\Album;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Photo\Rules\MaximumMediaPerUpload;
use MetaFox\Photo\Rules\UploadedAlbumItems;
use MetaFox\Photo\Support\Traits\PhotoExtraInfo;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxFileType;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\User\Support\Facades\UserEntity;

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

        return [
            'name'     => ['required', 'string', new ResourceNameRule('photo.album')],
            'text'     => ['sometimes', 'nullable', 'string'],
            'owner_id' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'privacy'  => ['sometimes', new PrivacyRule()],
            'items'    => ['array', new UploadedAlbumItems(), new MaximumMediaPerUpload((int) $maxPerUpload)],
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function validated($key = null, $default = null)
    {
        $context = user();
        $data    = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data    = Arr::add($data, 'owner_id', 0);
        $ownerId = Arr::get($data, 'owner_id');
        $owner   = $ownerId > 0 ? UserEntity::getById($ownerId)->detail : $context;

        Arr::set($data, 'owner', $owner);

        if (array_key_exists('items', $data)) {
            $data['items'] = $this->handleAlbumItems($data['items'], $context, $owner);
        }

        return $this->transformExtraPhotoInfo($data, 'items');
    }

    /**
     * @param  array<string, mixed>             $items
     * @param  User|null                        $owner
     * @param  User|null                        $context
     * @return array<int, array<string, mixed>>
     */
    protected function handleAlbumItems(array $items, ?User $context = null, ?User $owner = null): array
    {
        $allowOtherUpload = Settings::get('photo.photo_allow_uploading_video_to_photo_album', true);

        return collect($items)
            ->filter(function (array $item) use ($context, $owner, $allowOtherUpload) {
                $canUpload = app('events')->dispatch(
                    'photo.album.can_upload_to_album',
                    [$context, $owner, Arr::get($item, 'type')],
                    true
                );

                if (!$canUpload) {
                    return false;
                }

                return $allowOtherUpload || MetaFoxFileType::PHOTO_TYPE == $item['type'];
            })
            ->values()
            ->groupBy('status')
            ->toArray();
    }
}
