<?php

namespace MetaFox\Comment\Http\Requests\v1\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Comment\Traits\HandleTagFriendTrait;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    use HandleTagFriendTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'text'     => ['sometimes', 'string', 'nullable'],
            'photo_id' => ['sometimes', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'is_hide'  => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];

        $rules['sticker_id'] = ['sometimes', new ExistIfGreaterThanZero('exists:stickers,id')];

        if (app_active('metafox/giphy')) {
            $rules['giphy_gif_id'] = ['sometimes', 'nullable'];
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['text'] = trim(Arr::get($data, 'text', ''));
        if (!empty($data['text'])) {
            $data['tagged_friends'] = $this->handleTaggedFriend($data);
        }

        if (!Arr::has($data, 'photo_id')) {
            return $this->transformWithoutPhotoId($data);
        }

        return $this->transformWithPhotoId($data);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function transformWithoutPhotoId(array $data): array
    {
        if (!Arr::has($data, 'sticker_id')) {
            return $data;
        }

        if ($data['sticker_id'] == null) {
            Arr::set($data, 'sticker_id', 0);
        }

        if ($data['sticker_id'] > 0) {
            Arr::forget($data, 'photo_id');
        }

        if ($data['sticker_id'] == 0) {
            return $data;
        }

        if (!app_active('metafox/sticker')) {
            Arr::forget($data, 'sticker_id');
        }

        if (!app_active('metafox/giphy')) {
            Arr::forget($data, 'giphy_gif_id');
        }

        return $data;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function transformWithPhotoId(array $data): array
    {
        if ($data['photo_id'] > 0) {
            Arr::forget($data, 'sticker_id');
        }

        if ($data['photo_id'] == null) {
            Arr::set($data, 'photo_id', 0);

            if (Arr::has($data, 'sticker_id') && $data['sticker_id'] == 0) {
                Arr::forget($data, 'sticker_id');
            }

            if (!app_active('metafox/sticker')) {
                Arr::forget($data, 'sticker_id');
            }

            if (!app_active('metafox/giphy')) {
                Arr::forget($data, 'giphy_gif_id');
            }
        }

        return $data;
    }
}
