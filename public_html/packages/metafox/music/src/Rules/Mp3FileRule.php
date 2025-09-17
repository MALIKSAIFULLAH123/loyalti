<?php

namespace MetaFox\Music\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class Mp3FileRule implements Rule
{
    /**
     * @var string|null
     */
    private ?string $message;

    public function __construct()
    {
        $this->message = __p('music::validation.file_is_a_required_field');
    }

    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $id       = (int) Arr::get($value, 'id', 0);
        $tempFile = Arr::get($value, 'temp_file');
        $mime     = Arr::get($value, 'file_type');
        $fileName = Arr::get($value, 'file_name');

        if ($id > 0) {
            return true;
        }

        $validator = Validator::make(['temp_file' => $tempFile, 'file_name' => $fileName], [
            'temp_file' => ['required', 'numeric', 'exists:storage_files,id'],
            'file_name' => ['required', 'string'],
        ]);

        if (!$validator->passes()) {
            return false;
        }

        $this->message = __p('music::validation.file_must_be_mp3');

        if ($mime != 'audio/mpeg') {
            return false;
        }

        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        if ($ext != 'mp3') {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }
}
