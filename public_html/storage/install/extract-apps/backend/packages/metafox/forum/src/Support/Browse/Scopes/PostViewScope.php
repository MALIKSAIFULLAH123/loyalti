<?php

namespace MetaFox\Forum\Support\Browse\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Policies\ForumPostPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class PostViewScope extends BaseScope
{
    public const VIEW_DEFAULT            = Browse::VIEW_ALL;
    public const VIEW_CONTINUE_LAST_READ = 'continue_last_read';

    /**
     * @var string
     */
    protected $view;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $owner;

    /**
     * @var int
     */
    protected $profileId = 0;

    /**
     * @return array
     */
    public static function getAllowView(): array
    {
        return [
            Browse::VIEW_ALL,
            Browse::VIEW_MY,
            Browse::VIEW_PENDING,
            Browse::VIEW_MY_PENDING,
            self::VIEW_CONTINUE_LAST_READ,
        ];
    }

    /**
     * @param  User  $owner
     * @return $this
     */
    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param  int   $id
     * @return $this
     */
    public function setProfileId(int $id): self
    {
        $this->profileId = $id;

        return $this;
    }

    /**
     * @param  string|null $view
     * @return $this
     */
    public function setView(?string $view = null): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param  User  $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        $view    = $this->view;
        $context = $this->user;

        switch ($view) {
            case Browse::VIEW_MY:
                $builder->where('forum_posts.is_approved', 1)
                    ->where('forum_posts.user_id', '=', $context->entityId());
                break;
            case Browse::VIEW_PENDING:
                policy_authorize(ForumPostPolicy::class, 'approve', $context);

                $builder->where('forum_posts.is_approved', '=', 0);
                break;
            case Browse::VIEW_MY_PENDING:
                $builder->where('forum_posts.is_approved', '=', 0)
                    ->where('forum_posts.user_id', '=', $context->entityId());
                break;
            case Browse::VIEW_SEARCH:
                if (!$context->hasPermissionTo('forum_post.approve')) {
                    $builder->where(function (Builder $builder) use ($context) {
                        $builder->where('forum_posts.is_approved', '=', 1)
                            ->orWhere('forum_posts.user_id', '=', $context->entityId());
                    });
                }

                break;
            default:
                $builder->where('forum_posts.is_approved', '=', 1);
                break;
        }
    }
}
