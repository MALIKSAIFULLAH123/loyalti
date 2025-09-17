<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Attachment\Listeners;

use MetaFox\Attachment\Policies\Handlers\CanDownloadAttachment;
use MetaFox\Platform\Support\BasePackageSettingListener;

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
class PackageSettingListener extends BasePackageSettingListener
{
    public function getEvents(): array
    {
        return [
            'attachment.verify_file_type' => [
                VerifyFileTypeListener::class,
            ],
        ];
    }

    public function getPolicyHandlers(): array
    {
        return [
            'downloadAttachment' => CanDownloadAttachment::class,
        ];
    }
}
