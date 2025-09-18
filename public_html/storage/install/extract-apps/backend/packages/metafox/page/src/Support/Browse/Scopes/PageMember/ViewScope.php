<?php

namespace MetaFox\Page\Support\Browse\Scopes\PageMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_ADMIN  = 'admin';
    public const VIEW_MEMBER = 'member';
    public const VIEW_ALL    = 'all';
    public const VIEW_FRIEND = 'friend';

    /**
     * @var int
     */
    private int $pageId;

    /**
     * @var bool
     */
    private bool $isViewAdmin = false;

    /**
     * @var bool
     */
    private bool $isViewMember = false;

    /**
     * @var bool
     */
    private bool $isViewFriend = false;

    /**
     * @var string
     */
    private string $view = self::VIEW_ALL;

    /**
     * @var ?User
     */
    protected ?User $user = null;

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_ADMIN,
            self::VIEW_MEMBER,
            self::VIEW_ALL,
            self::VIEW_FRIEND,
        ];
    }

    /**
     * @return bool
     */
    public function isViewAdmin(): bool
    {
        return $this->isViewAdmin;
    }

    /**
     * @param bool $isViewAdmin
     *
     * @return ViewScope
     */
    public function setIsViewAdmin(bool $isViewAdmin): self
    {
        $this->isViewAdmin = $isViewAdmin;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewMember(): bool
    {
        return $this->isViewMember;
    }

    /**
     * @param bool $isViewMember
     *
     * @return ViewScope
     */
    public function setIsViewMember(bool $isViewMember): self
    {
        $this->isViewMember = $isViewMember;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewFriend(): bool
    {
        return $this->isViewFriend;
    }

    /**
     * @param bool $isViewFriend
     *
     * @return ViewScope
     */
    public function setIsViewFriend(bool $isViewFriend): self
    {
        $this->isViewFriend = $isViewFriend;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @param int $pageId
     *
     * @return ViewScope
     */
    public function setPageId(int $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return ViewScope
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return ?User
     */
    public function getUserContext(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ViewScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();

        $table = $model->getTable();

        $builder->where('page_id', $this->getPageId());

        $userContext = $this->getUserContext();

        if ($this->isViewAdmin() || $view == self::VIEW_ADMIN) {
            $builder->where("$table.member_type", PageMember::ADMIN);
        }

        if ($this->isViewMember() || $view == self::VIEW_MEMBER) {
            $builder->where("$table.member_type", PageMember::MEMBER);
        }

        if ($this->isViewFriend() || $view == self::VIEW_FRIEND) {
            $builder->join('friends', function (JoinClause $join) use ($userContext, $table) {
                $join->on('friends.user_id', '=', $table . '.user_id');
                $join->where('friends.owner_id', '=', $userContext->entityId());
            });
        }

        $builder->join('users', 'users.id', '=', "$table.user_id")
            ->orderBy('users.full_name');
    }
}
