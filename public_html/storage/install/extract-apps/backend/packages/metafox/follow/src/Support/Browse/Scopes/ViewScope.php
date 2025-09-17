<?php

namespace MetaFox\Follow\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 */
class ViewScope extends BaseScope
{
    public const VIEW_FOLLOWING = 'following';
    public const VIEW_FOLLOWER  = 'follower';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_FOLLOWING,
            self::VIEW_FOLLOWER,
        ];
    }

    /**
     * @var string
     */
    private string $view = self::VIEW_FOLLOWER;

    /**
     * @var User
     */
    private User $userContext;

    /**
     * @var int
     */
    protected int $profileId = 0;

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
     * @return int
     */
    public function getProfileId(): int
    {
        return $this->profileId;
    }

    /**
     * @param int $profileId
     *
     * @return ViewScope
     */
    public function setProfileId(int $profileId): self
    {
        $this->profileId = $profileId;

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
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $view      = $this->getView();
        $context   = $this->getUserContext();
        $contextId = $context->entityId();
        $profileId = $this->getProfileId();

        switch ($view) {
            case self::VIEW_FOLLOWER:
                $builder->where('follows.user_id', $contextId);
                break;
            case self::VIEW_FOLLOWING:
                $builder->where('follows.owner_id', $contextId);
                break;
            default:
                $builder->where('follows.user_id', $profileId);
        }
    }
}
