<?php

namespace MetaFox\Profile\Jobs\Traits;

use Illuminate\Support\Str;

trait UniqueNameTrait
{
    public function parseUniqueCustomFieldName(string $name): string
    {
        //lower string all character.
        $newName = Str::lower($name);

        //replace all repeating undercores into one undercores
        $newName = preg_replace('%[_-]+%', '_', $newName);

        //remove all special character excepts undercores.
        $newName = preg_replace('%[^_a-z0-9]+%', '', $newName);

        //trim all undercores at begining and end of the string.
        $newName = trim($newName, '_');

        return is_string($newName) ? $newName : md5($name);
    }
}
