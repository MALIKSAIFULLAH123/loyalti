<?php

namespace MetaFox\Page\Support\Browse\Traits\PageMember;

use MetaFox\Page\Models\PageMember;
use MetaFox\Page\Policies\PageMemberPolicy;
use MetaFox\Platform\Facades\PolicyGate;

trait ExtraTrait
{
    public function getExtra(): array
    {
        $context = user();

        /** @var PageMemberPolicy $policy */
        $policy = PolicyGate::getPolicyFor(PageMember::class);

        return [
            'can_reassign_owner'      => $policy->reassignOwner($context, $this->resource),
            'can_remove_member'       => $policy->removeMemberFromPage($context, $this->resource),
            'can_set_as_admin'        => $policy->setMemberAsAdmin($context, $this->resource),
            'can_remove_as_admin'     => $policy->removeAsAdmin($context, $this->resource),
            'can_block'               => $policy->blockFromPage($context, $this->resource),
            'can_cancel_admin_invite' => $policy->cancelInvite($this->resource),
        ];
    }
}
