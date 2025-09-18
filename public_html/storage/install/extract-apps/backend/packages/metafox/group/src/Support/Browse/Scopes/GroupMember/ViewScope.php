<?php

namespace MetaFox\Group\Support\Browse\Scopes\GroupMember;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Member;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_ADMIN            = 'admin';
    public const VIEW_MODERATOR        = 'moderator';
    public const VIEW_INVITE_ADMIN     = 'invite_admin';
    public const VIEW_INVITE_MODERATOR = 'invite_moderator';
    public const VIEW_MEMBER           = 'member';
    public const VIEW_ALL              = 'all';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_ADMIN,
            self::VIEW_MODERATOR,
            self::VIEW_MEMBER,
            self::VIEW_ALL,
            self::VIEW_INVITE_MODERATOR,
            self::VIEW_INVITE_ADMIN,
        ];
    }

    /**
     * @var string
     */
    private string $view = self::VIEW_ALL;

    /**
     * @var int
     */
    private int $groupId;

    /**
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     *
     * @return ViewScope
     */
    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();

        $table = $model->getTable();

        $builder->where("{$table}.group_id", $this->getGroupId());

        switch ($view) {
            case self::VIEW_ADMIN:
                $builder->where("$table.member_type", Member::ADMIN);
                break;
            case self::VIEW_MODERATOR:
                $builder->where("$table.member_type", Member::MODERATOR);
                break;
            case self::VIEW_MEMBER:
            case self::VIEW_INVITE_MODERATOR:
                $builder->where("$table.member_type", Member::MEMBER);
                break;
            case self::VIEW_INVITE_ADMIN:
                $builder->whereIn("$table.member_type", [Member::MEMBER, Member::MODERATOR]);
                break;
        }

        $builder->join('users', 'users.id', '=', "$table.user_id");
    }
}
