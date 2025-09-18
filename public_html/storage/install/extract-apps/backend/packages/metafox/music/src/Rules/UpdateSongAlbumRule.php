<?php

namespace MetaFox\Music\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class UpdateSongAlbumRule implements Rule
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

        $songs = collect($value)
            ->values()
            ->groupBy('status')
            ->toArray();

        $totalSong  = count($value);
        $removeSong = count(Arr::get($songs, 'remove') ?? []);
        $newSong    = count(Arr::get($songs, 'new') ?? []);

        if ($totalSong - $removeSong < 1) {
            return false;
        }

        if ($newSong > $this->maxFiles && $this->maxFiles > 0) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return __p('music::validation.the_number_of_songs_of_a_album_must_be_greater_than_or_equal_to_1');
    }
}
