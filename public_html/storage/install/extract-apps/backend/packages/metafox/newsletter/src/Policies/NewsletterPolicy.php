<?php

namespace MetaFox\Newsletter\Policies;

use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User as User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class NewsletterPolicy.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class NewsletterPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;

    protected string $type = Newsletter::class;

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function view(User $user, Entity $resource): bool
    {
        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Newsletter) {
            return false;
        }

        return $resource->status === Newsletter::NOT_STARTED_STATUS;
    }

    public function resend(Newsletter $newsletter): bool
    {
        return $newsletter->status === Newsletter::COMPLETED_STATUS;
    }

    public function stop(Newsletter $newsletter): bool
    {
        return in_array($newsletter->status, [Newsletter::SENDING_STATUS, Newsletter::PENDING_STATUS]);
    }

    public function process(Newsletter $newsletter): bool
    {
        return $newsletter->status === Newsletter::NOT_STARTED_STATUS;
    }

    public function reprocess(Newsletter $newsletter): bool
    {
        return $newsletter->status === Newsletter::STOPPED_STATUS;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if (!$resource instanceof Newsletter) {
            return false;
        }

        return $resource->status !== Newsletter::SENDING_STATUS;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return true;
    }
}
