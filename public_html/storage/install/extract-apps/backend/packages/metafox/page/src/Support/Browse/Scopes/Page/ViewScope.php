<?php

namespace MetaFox\Page\Support\Browse\Scopes\Page;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT       = Browse::VIEW_ALL;
    public const VIEW_LIKED         = 'liked';
    public const VIEW_INVITED       = 'invited';
    public const VIEW_FRIEND_MEMBER = 'friend_member';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            Browse::VIEW_FRIEND,
            Browse::VIEW_PENDING,
            Browse::VIEW_FEATURE,
            Browse::VIEW_SPONSOR,
            self::VIEW_LIKED,
            self::VIEW_INVITED,
            self::VIEW_FRIEND_MEMBER,
            Browse::VIEW_MY_PENDING,
            Browse::VIEW_SEARCH,
        ];
    }

    /**
     * @var string
     */
    private string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    private User $userContext;

    /**
     * @var bool
     */
    private bool $isProfile = false;
    /**
     * @return bool
     */
    public function isViewProfile(): bool
    {
        return $this->isProfile;
    }

    /**
     * @param bool $isProfile
     *
     * @return ViewScope
     */
    public function setIsViewProfile(bool $isProfile): self
    {
        $this->isProfile = $isProfile;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->userContext;
    }

    /**
     * @param User $userContext
     *
     * @return ViewScope
     */
    public function setUserContext(User $userContext): self
    {
        $this->userContext = $userContext;

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
        if ($this->isViewProfile()) {
            return;
        }

        $view = $this->getView();

        $context   = $this->getUserContext();
        $contextId = $context->entityId();

        if ($contextId != MetaFoxConstant::GUEST_USER_ID) {
            $context = UserEntity::getById($contextId)->detail;
        }
        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where('pages.user_id', '=', $contextId)
                    ->where('pages.is_approved', Page::IS_APPROVED);
                break;
            case Browse::VIEW_FRIEND:
                $builder->join('friends AS f', function (JoinClause $join) use ($contextId) {
                    $join->on('f.user_id', '=', 'pages.user_id')
                        ->where('f.owner_id', '=', $contextId)
                        ->where('pages.is_approved', Page::IS_APPROVED);
                });
                break;
            case self::VIEW_FRIEND_MEMBER:
                $builder->whereIn("pages.id", function ($query) use ($contextId) {
                    $query->from("page_members")
                    ->select('page_members.page_id')
                    ->join("friends", 'friends.user_id', '=', 'page_members.user_id')
                    ->where('friends.owner_id', $contextId);
                });

                $builder->where('pages.is_approved', Page::IS_APPROVED);
                break;
            case Browse::VIEW_PENDING:
                $builder->whereNot('pages.is_approved', Page::IS_APPROVED);
                break;

            case self::VIEW_LIKED:
                $builder->join('page_members AS pm', function (JoinClause $join) use ($contextId) {
                    $join->on('pm.page_id', '=', 'pages.id')
                        ->where('pm.user_id', $contextId)
                        ->where('pages.is_approved', Page::IS_APPROVED);
                });
                break;
            case self::VIEW_INVITED:
                $builder->whereHas('invites', function (Builder $query) use ($contextId) {
                    $query->where('owner_id', '=', $contextId)
                        ->where('status_id', PageInvite::STATUS_PENDING)
                        ->where('invite_type', PageInvite::INVITE_MEMBER)
                        ->whereDate('expired_at', '>', Carbon::now());
                });
                $builder->where('pages.is_approved', Page::IS_APPROVED);
                break;

            case Browse::VIEW_MY_PENDING:
                $builder->whereNot('pages.is_approved', Page::IS_APPROVED)
                    ->where('pages.user_id', $contextId);
                break;

            case Browse::VIEW_SEARCH:
                if (!$context->hasPermissionTo('page.approve')) {
                    $builder->where(function (Builder $builder) use ($contextId) {
                        $builder->where('pages.is_approved', Page::IS_APPROVED)
                            ->orWhere('pages.user_id', '=', $contextId);
                    });
                }

                break;
            default:
                $builder->where('pages.is_approved', Page::IS_APPROVED);
        }
    }
}
