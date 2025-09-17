<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Support\Facades\Schema;

/**
 * Trait HasFollowTrait.
 */
trait HasFollowTrait
{
    public function incrementTotalFollowing(int $amount = 1): void
    {
        if (!Schema::hasColumn($this->getTable(), 'total_following')) {
            return;
        }

        $this->incrementAmount('total_following', $amount);
    }

    public function incrementTotalFollower(int $amount = 1): void
    {
        if (!Schema::hasColumn($this->getTable(), 'total_follower')) {
            return;
        }

        $this->incrementAmount('total_follower', $amount);
    }

    public function decrementTotalFollowing(int $amount = 1): void
    {
        if (!Schema::hasColumn($this->getTable(), 'total_following')) {
            return;
        }

        $this->decrementAmount('total_following', $amount);
    }

    public function decrementTotalFollower(int $amount = 1): void
    {
        if (!Schema::hasColumn($this->getTable(), 'total_follower')) {
            return;
        }

        $this->decrementAmount('total_follower', $amount);
    }
}
