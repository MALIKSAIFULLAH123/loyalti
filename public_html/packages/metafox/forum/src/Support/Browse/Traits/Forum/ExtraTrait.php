<?php

namespace MetaFox\Forum\Support\Browse\Traits\Forum;

use MetaFox\Forum\Models\Forum;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;

trait ExtraTrait
{
    public function getForumExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Forum::class);

        $context = user();

        $resource = $this->resource;

        return [
            ResourcePermission::CAN_EDIT   => $policy->update($context, $resource),
            ResourcePermission::CAN_DELETE => $policy->delete($context, $resource),
            'can_create_thread_as_mod'     => $policy->createThreadAsMod($context, $resource),
        ];
    }
}
