<?php

namespace MetaFox\Poll\Policies\Contracts;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User as User;

interface PollPolicyInterface
{
    public function vote(User $user, ?Content $resource = null): bool;

    public function changeVote(User $user, ?Content $resource = null): bool;

    public function viewHideVote(User $user, ?Content $resource = null): bool;

    public function viewResultBeforeVote(User $user, ?Content $resource = null): bool;

    public function viewResultAfterVote(User $user, ?Content $resource = null): bool;
}
