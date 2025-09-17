<?php

namespace MetaFox\Music\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxUploadSongAlbumRule implements Rule
{
    private int $maxFiles;

    public function __construct(int $maxFiles = 0)
    {
        $this->maxFiles = $maxFiles;
    }

    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $totalSong = count($value);

        if ($this->maxFiles == 0) {
            return true;
        }

        if ($totalSong > $this->maxFiles) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __p('music::phrase.maximum_per_upload_limit_reached', [
            'limit' => $this->maxFiles,
        ]);
    }
}
