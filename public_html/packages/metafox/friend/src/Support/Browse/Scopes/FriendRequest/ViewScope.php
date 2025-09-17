<?php

namespace MetaFox\Friend\Support\Browse\Scopes\FriendRequest;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Friend\Models\FriendRequest;
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
    public const VIEW_SEND = 'send';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_SEND, // Get all request you sent
            Browse::VIEW_PENDING, //Get all incoming friend requests
        ];
    }

    /**
     * @var string
     */
    protected string $view = self::VIEW_SEND;

    /**
     * @var User
     */
    protected User $user;

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
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table       = $model->getTable();
        $view        = $this->getView();
        $userContext = $this->getUserContext();

        switch ($view) {
            case Browse::VIEW_PENDING://Get all incoming friend requests
                $builder->where($this->alias($table, 'owner_id'), '=', $userContext->entityId())
                    ->where($this->alias($table, 'is_deny'), '<>', FriendRequest::IS_DENY);
                break;
            case self::VIEW_SEND:// Get all request you sent
                $builder->leftJoin('users', function (JoinClause $joinClause) use ($table) {
                    $joinClause->on('users.id', '=', $this->alias($table, 'owner_id'));
                });
                $builder->whereNotNull('users.id');
                $builder->where($this->alias($table, 'user_id'), '=', $userContext->entityId());
                break;
            default:
                $builder->where($this->alias($table, 'user_id'), '=', $userContext->entityId());
        }
    }
}
