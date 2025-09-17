<?php

namespace MetaFox\Group\Support\Browse\Scopes\Invite;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Group\Support\InviteType;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_ADMIN     = 'admin';
    public const VIEW_MODERATOR = 'moderator';
    public const VIEW_MEMBERS   = 'members';
    public const VIEW_ALL       = 'all';

    private string $view = self::VIEW_ALL;
    private User   $userContext;

    public function getUserContext(): User
    {
        return $this->userContext;
    }

    public function setUserContext(User $userContext): void
    {
        $this->userContext = $userContext;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    /**
     * @return array
     */
    public static function getViewOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.all'),
                'value' => self::VIEW_ALL,
            ],
            [
                'label' => __p('group::phrase.invite_type_label', ['invite_type' => InviteType::INVITED_ADMIN_GROUP]),
                'value' => self::VIEW_ADMIN,
            ],
            [
                'label' => __p('group::phrase.invite_type_label', ['invite_type' => InviteType::INVITED_MODERATOR_GROUP]),
                'value' => self::VIEW_MODERATOR,
            ],
            [
                'label' => __p('group::phrase.invite_type_label', ['invite_type' => InviteType::INVITED_MEMBER]),
                'value' => self::VIEW_MEMBERS,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_ADMIN,
            self::VIEW_MODERATOR,
            self::VIEW_ALL,
            self::VIEW_MEMBERS,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view = $this->getView();

        switch ($view) {
            case self::VIEW_MEMBERS:
                $builder->whereIn('invite_type', [InviteType::INVITED_MEMBER, InviteType::INVITED_GENERATE_LINK]);
                $builder->where(function (Builder $builder) {
                    $builder->whereNull('expired_at')
                        ->orWhere('expired_at', '>=', Carbon::now()->toDateTimeString());
                });
                break;
            case self::VIEW_ADMIN:
                $builder->where('invite_type', InviteType::INVITED_ADMIN_GROUP);
                break;
            case self::VIEW_MODERATOR:
                $builder->where('invite_type', InviteType::INVITED_MODERATOR_GROUP);
                break;
        }
    }
}
