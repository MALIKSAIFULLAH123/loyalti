<?php

namespace MetaFox\Group\Support\Browse\Scopes\GroupMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class ViewScope.
 */
class MentionScope extends BaseScope
{
    /**
     * @var User
     */
    protected $context;

    /**
     * @var int
     */
    protected int $groupId;

    /**
     * @var string|null
     */
    protected $search;

    /**
     * @var bool
     */
    protected $isMention = true;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var bool
     */
    protected $isIncludeContext = false;

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->groupId;
    }

    /**
     * @param  int   $groupId
     * @return $this
     */
    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * @param  string|null $search
     * @return $this
     */
    public function setSearch(?string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * @param  bool  $value
     * @return $this
     */
    public function setIsMention(bool $value): self
    {
        $this->isMention = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMention(): bool
    {
        return $this->isMention;
    }

    /**
     * @param  bool  $value
     * @return $this
     */
    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param  User  $context
     * @return $this
     */
    public function setContext(User $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getContext(): ?User
    {
        return $this->context;
    }

    /**
     * @param  bool  $value
     * @return $this
     */
    public function setIsIncludeContext(bool $value): self
    {
        $this->isIncludeContext = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsIncludeContext(): bool
    {
        return $this->isIncludeContext;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $search = $this->getSearch();

        $groupId = $this->getGroupId();

        $table = $this->getTable();

        $isMention = $this->getIsMention();

        $context = $this->getContext();

        $ignoreContext = !$this->getIsIncludeContext() && null !== $context;

        $builder->join('group_members AS gm', function (JoinClause $joinClause) use ($table, $groupId) {
            $joinClause->on('gm.user_id', '=', $table . '.id')
                ->where('gm.group_id', '=', $groupId);
        });

        if ($ignoreContext) {
            $builder->where('gm.user_id', '<>', $context->entityId());
        }

        if (is_string($search) && $search != '') {
            $builder = $builder->addScope(new SearchScope($search, ['user_name', 'full_name'], $table));
        }

        if ($isMention) {

            // Who can tag me in written contexts?
            $builder->leftJoin('user_privacy_values as can_be_tagged', function (JoinClause $join) use ($table) {
                $join->on('gm.user_id', '=', 'can_be_tagged.user_id');
                $join->where('can_be_tagged.name', '=', 'user:can_i_be_tagged');
                $join->where('can_be_tagged.privacy', '=', MetaFoxPrivacy::ONLY_ME);
            });

            $builder->whereNull('can_be_tagged.id');

            // Who can share a post on your wall?
            $builder->leftJoin('user_privacy_values as share_a_post_on_wall', function (JoinClause $join) use ($table) {
                $join->on('gm.user_id', '=', 'share_a_post_on_wall.user_id');
                $join->where('share_a_post_on_wall.name', '=', 'feed:share_on_wall');
                $join->where('share_a_post_on_wall.privacy', '=', MetaFoxPrivacy::ONLY_ME);
            });

            $builder->whereNull('share_a_post_on_wall.id');
        }
    }
}
