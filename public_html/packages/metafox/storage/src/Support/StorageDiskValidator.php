<?php

namespace MetaFox\Storage\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class StorageDiskValidator
{
    /**
     * @param  array            $data
     * @return bool
     * @throws RuntimeException
     */
    public static function isValid(array $data): bool
    {
        $options = Arr::pull($data, 'options', []);
        $data['throw'] = true;
        $disk          = Storage::build($data);
        $content = date('Ymdhis');
        $path    = sprintf('/temp/%s.txt', $content);

        if (!$disk->put($path, $content, $options)) {
            throw new RuntimeException(__p('storage::phrase.invalid_configuration'));
        }

        if (!$disk->get($path)) {
            throw new RuntimeException(__p('storage::phrase.invalid_configuration'));
        }

        if (!$disk->delete($path)) {
            throw new RuntimeException(__p('storage::phrase.invalid_configuration'));
        }

        if (!$disk->url($path)) {
            throw new RuntimeException(__p('storage::phrase.invalid_configuration'));
        }

        return true;
    }
}
