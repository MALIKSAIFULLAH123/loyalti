<?php

namespace MetaFox\Photo\Support\Converters;

use MetaFox\Platform\Contracts\FileConverterInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class HEICImageConverter implements FileConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(UploadedFile $target): ?UploadedFile
    {
        $targetPath    = $target->getPath() . DIRECTORY_SEPARATOR . $target->getFilename();
        $convertedFile = tempnam(sys_get_temp_dir(), 'heic_converted_');

        if (!class_exists('\Maestroerror\HeicToJpg')) {
            Log::error('Missing package maestroerror/php-heic-to-jpg');

            return null;
        }

        if (!\Maestroerror\HeicToJpg::isHeic($targetPath)) {
            return null;
        }

        try {
            \Maestroerror\HeicToJpg::convert($targetPath)->saveAs($convertedFile);
        } catch (\Throwable $error) {
            Log::channel('dev')->error($error->getMessage());
        }

        if (!File::exists($convertedFile)) {
            return null;
        }

        return upload()->asUploadedFile($convertedFile);
    }

    /**
     * @return array<string>|null
     */
    public function supportedMimeTypes(): ?array
    {
        return ['image/heic', 'image/heif'];
    }
}
