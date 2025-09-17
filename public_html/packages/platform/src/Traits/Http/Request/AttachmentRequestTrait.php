<?php

namespace MetaFox\Platform\Traits\Http\Request;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\AllowMaxFilesRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * Trait HasFeedParam.
 *
 * @property Content $resource
 */
trait AttachmentRequestTrait
{
    /**
     * @param array<string, mixed> $rules
     *
     * @return array<string, mixed>
     */
    protected function applyAttachmentRules(array $rules): array
    {
        $maxFiles = Settings::get('core.attachment.maximum_number_of_attachments_that_can_be_uploaded', 5);

        $allowStatusRule = [MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_REMOVE_STATUS];

        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.13', '<=')) {
            $allowStatusRule = array_merge($allowStatusRule, [MetaFoxConstant::FILE_CREATE_STATUS]);
        }

        return array_merge($rules, [
            'attachments'          => ['sometimes', 'array', new AllowMaxFilesRule($maxFiles)],
            'attachments.*'        => ['sometimes', 'array'],
            'attachments.*.id'     => ['sometimes', new ExistIfGreaterThanZero('exists:core_attachments,id')],
            'attachments.*.status' => ['sometimes', 'string', new AllowInRule($allowStatusRule)],
        ]);
    }
}
