<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Http\UploadedFile;

interface FileConverterInterface
{
    /**
     * @param  UploadedFile      $target
     * @return UploadedFile|null
     */
    public function convert(UploadedFile $target): ?UploadedFile;

    /**
     * @return array<string>|null
     */
    public function supportedMimeTypes(): ?array;
}
