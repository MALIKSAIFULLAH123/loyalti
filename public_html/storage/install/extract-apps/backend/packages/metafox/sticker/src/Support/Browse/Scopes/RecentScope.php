<?php

namespace MetaFox\Sticker\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class RecentScope.
 * @ignore
 * @codeCoverageIgnore
 */
class RecentScope extends BaseScope
{
    private ?User $user = null;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = $this->getUser();

        if ($user) {
            $builder->where('user_id', '=', $user->entityId());
        }

        $builder->join('sticker_recent as sr', function (JoinClause $join) {
            $join->on('sr.sticker_id', '=', 'stickers.id');
        })
        ->orderBy('sr.updated_at', 'desc')
        ->orderBy('sr.id', 'desc');
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
