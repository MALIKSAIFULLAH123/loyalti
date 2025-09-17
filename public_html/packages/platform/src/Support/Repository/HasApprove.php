<?php

namespace MetaFox\Platform\Support\Repository;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPolicy;
use MetaFox\Platform\Contracts\User;

/**
 * Trait HasApprove.
 * @codeCoverageIgnore
 */
trait HasApprove
{
    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content
    {
        $resource = $this->find($id);

        if ($resource instanceof HasPolicy) {
            gate_authorize($context, 'approve', $resource, $resource);
        }

        $success = $resource->update(['is_approved' => 1]);

        if ($success) {
            app('events')->dispatch('models.notify.approved', [$context, $resource], true);
        }

        return $resource->refresh();
    }

    /**
     * @param Content $model
     *
     * @return bool
     */
    public function isPending(Content $model): bool
    {
        if (!$model instanceof \MetaFox\Platform\Contracts\HasApprove) {
            return false;
        }

        return $model->is_approved == 0;
    }
}
