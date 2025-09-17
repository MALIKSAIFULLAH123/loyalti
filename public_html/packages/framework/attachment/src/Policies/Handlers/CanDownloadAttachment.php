<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Attachment\Policies\Handlers;

use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Models\Attachment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanDownloadAttachment implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource instanceof Attachment) {
            return false;
        }

        if ($resource?->item_id && !$resource?->item instanceof HasTotalAttachment) {
            return false;
        }
        
        if (!PolicyGate::check($entityType, 'view', [$user, $resource])) {
            return false;
        }

        if (!$user->hasPermissionTo("$entityType.download_attachment")) {
            return false;
        }

        return true;
    }
}
