<?php

namespace MetaFox\Core\Support\Converters;

use MetaFox\Platform\Contracts\FileConverterInterface;
use Illuminate\Http\UploadedFile;

class NoneConverter implements FileConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(UploadedFile $target): ?UploadedFile
    {
        return null;
    }

    /**
     * @return array<string>|null
     */
    public function supportedMimeTypes(): ?array
    {
        return null;
    }
}
