<?php

namespace MetaFox\Platform\Traits\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Facades\User as UserSupport;


/**
 * Trait HasStatistic.
 * @property Model $resource
 */
trait HasStatistic
{
    /**
     * @return array<string, mixed>
     */
    protected function getStatistic(): array
    {
        return [
            'total_view'    => $this->resource instanceof HasTotalView ? $this->resource->total_view : 0,
            'total_like'    => $this->viewReaction() ? $this->resource->total_like : null,
            'total_comment' => $this->resource instanceof HasTotalComment ? $this->resource->total_comment : 0,
            'total_reply'   => $this->resource instanceof HasTotalCommentWithReply ? $this->resource->total_reply : 0,
            'total_share'   => $this->resource instanceof HasTotalShare ? $this->resource->total_share : 0,
        ];
    }

    protected function viewReaction(): bool
    {
        $user = Auth::guest() ? UserSupport::getGuestUser() : Auth::user();

        if (!method_exists($this, 'canViewReaction')) {
            return true;
        }

        if (!$this->resource instanceof Content) {
            return false;
        }

        if ($user instanceof User) {
            return $this->canViewReaction($user, $this->resource);
        }

        return false;
    }
}
