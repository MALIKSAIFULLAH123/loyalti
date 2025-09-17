<?php

namespace MetaFox\Photo\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;
use MetaFox\Photo\Support\Traits\PhotoExtraInfo;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;

class UploadedAlbumItems implements RuleContract
{
    use PhotoExtraInfo;

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $items            = collect($value)->groupBy('status')->toArray();
        $allowOtherUpload = Settings::get('photo.photo_allow_uploading_video_to_photo_album', true);
        $types            = ['photo'];

        if ($allowOtherUpload) {
            $types[] = 'video';
        }
        $rules = [
            'new'             => ['array', 'nullable'],
            'new.*.id'        => ['required', 'numeric', 'exists:storage_files,id'],
            'new.*.type'      => ['required', 'string', new AllowInRule($types)],
            'new.*.status'    => ['required', 'string'],
            'update'          => ['array', 'nullable'],
            'update.*.id'     => ['required', 'numeric'],
            'update.*.type'   => ['required', 'string'],
            'update.*.status' => ['required', 'string'],
            'remove'          => ['array', 'nullable'],
            'remove.*.id'     => ['required', 'numeric'],
            'remove.*.type'   => ['required', 'string'],
            'remove.*.status' => ['required', 'string'],
        ];

        $rules = $this->addExtraPhotoInfoRule($rules, 'new');
        $rules = $this->addExtraPhotoInfoRule($rules, 'update');

        $validator = Validator::make($items, $rules);

        return $validator->passes();
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return __p('photo::validation.album_items_are_invalid');
    }
}
