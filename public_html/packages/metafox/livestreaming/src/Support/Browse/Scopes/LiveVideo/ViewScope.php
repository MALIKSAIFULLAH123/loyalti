<?php

namespace MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT       = Browse::VIEW_ALL;
    public const VIEW_ALL_STREAMING = 'all_streaming';
    public const VIEW_MY_STREAMING  = 'my_streaming';

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowView())];
    }

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
            Browse::VIEW_SEARCH,
            Browse::VIEW_MY_PENDING,
            self::VIEW_ALL_STREAMING,
            self::VIEW_MY_STREAMING,
        ];
    }

    /**
     * @var string
     */
    protected string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    protected User $user;

    /**
     * @var bool
     */
    protected bool $isViewOwner = false;

    /**
     * @var int
     */
    protected int $profileId = 0;

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
     * @return bool
     */
    public function isViewOwner(): bool
    {
        return $this->isViewOwner;
    }

    /**
     * @param bool $isViewOwner
     *
     * @return ViewScope
     */
    public function setIsViewOwner(bool $isViewOwner): self
    {
        $this->isViewOwner = $isViewOwner;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserContext(): User
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
        if ($this->isViewOwner()) {
            return;
        }

        $view = $this->getView();

        $userContext = $this->getUserContext();

        // filter view_id != 2 (not livestream yet)
        $builder->where('livestreaming_live_videos.view_id', '!=', 2);

        switch ($view) {
            case self::VIEW_ALL_STREAMING:
                $builder->where('livestreaming_live_videos.is_streaming', '=', 1)
                    ->where('livestreaming_live_videos.is_approved', '=', 1);
                break;
            case self::VIEW_MY_STREAMING:
                $builder->where('livestreaming_live_videos.is_approved', '=', 1)
                    ->where('livestreaming_live_videos.is_streaming', 1)
                    ->where(function (Builder $whereQuery) use ($userContext) {
                        $whereQuery->where('livestreaming_live_videos.owner_id', '=', $userContext->entityId())
                            ->orWhere('livestreaming_live_videos.user_id', '=', $userContext->entityId());
                    });
                break;
            case Browse::VIEW_MY:
                $builder->where('livestreaming_live_videos.is_approved', 1)
                    ->where(function (Builder $whereQuery) use ($userContext) {
                        $whereQuery->where('livestreaming_live_videos.owner_id', '=', $userContext->entityId())
                            ->orWhere('livestreaming_live_videos.user_id', '=', $userContext->entityId());
                    });
                break;
            case Browse::VIEW_FRIEND:
                $builder->join('friends AS f', function (JoinClause $join) use ($userContext) {
                    $join->on('f.user_id', '=', 'livestreaming_live_videos.owner_id')
                        ->where([
                            ['f.owner_id', '=', $userContext->entityId()],
                            ['livestreaming_live_videos.is_approved', '=', 1],
                        ]);
                });
                break;
            case Browse::VIEW_PENDING:
                $builder->where('livestreaming_live_videos.is_approved', '!=', 1);

                break;
            case Browse::VIEW_MY_PENDING:
                $builder->whereNot('livestreaming_live_videos.is_approved', 1)
                    ->where('livestreaming_live_videos.user_id', $userContext->entityId());
                break;
            case Browse::VIEW_SEARCH:
                if (!$userContext->hasPermissionTo('live_video.approve')) {
                    $builder->where(function (Builder $subQuery) use ($userContext) {
                        $subQuery->where('livestreaming_live_videos.is_approved', '=', 1)
                            ->orWhere('livestreaming_live_videos.user_id', '=', $userContext->entityId());
                    });
                }

                break;
            default:
                $builder->where('livestreaming_live_videos.is_approved', '=', 1);
        }
    }
}
