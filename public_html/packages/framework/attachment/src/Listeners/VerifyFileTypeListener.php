<?php

namespace MetaFox\Attachment\Listeners;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/PackageSettingListener.stub.
 */

/**
 * Class PackageSettingListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class VerifyFileTypeListener
{
    /**
     * @param User|null            $context
     * @param UploadedFile|null    $file
     * @param array<string, mixed> $attributes
     */
    public function handle(?User $context = null, ?UploadedFile $file = null, array $attributes = []): ?string
    {
        if (!$context instanceof User) {
            return null;
        }

        if (!$file instanceof UploadedFile) {
            return null;
        }

        if (!Arr::has($attributes, 'item_type')) {
            return null;
        }

        $itemType = Arr::get($attributes, 'item_type');

        if ($this->attachmentRepository()->verifyAttachmentType($context, $file, $itemType)) {
            return null;
        }

        return __p('validation.cannot_play_back_the_file_the_format_is_not_supported');
    }

    public function attachmentRepository(): AttachmentRepositoryInterface
    {
        return resolve(AttachmentRepositoryInterface::class);
    }
}
