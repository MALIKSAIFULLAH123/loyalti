<?php

namespace MetaFox\Friend\Support\Browse\Scopes\Friend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class ViewFriendsScope.
 * @ignore
 * @codeCoverageIgnore
 */
class TagScope extends BaseScope
{
    /**
     * @var int
     */
    protected int $userId;

    /**
     * @var int|null
     */
    protected ?int $itemId = 0;

    /**
     * @var string|null
     */
    protected ?string $itemType = null;

    /**
     * @var string
     */
    protected string $searchText = '';

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return self
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return ?int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return self
     */
    public function setItemId(?int $itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @return self
     */
    public function setItemType(?string $itemType)
    {
        $this->itemType = $itemType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchText(): string
    {
        return $this->searchText;
    }

    /**
     * @param string $searchText
     *
     * @return self
     */
    public function setSearchText(string $searchText): self
    {
        $this->searchText = $searchText;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $userId = $this->getUserId();

        $itemId = $this->getItemId();

        $itemType = $this->getItemType();

        $searchText = $this->getSearchText();

        $builder->select('users.*')
            ->leftJoin('friends', function (JoinClause $join) use ($userId) {
                $join->on('users.id', '=', 'friends.owner_id');
                $join->where('friends.user_id', $userId);
            })
            ->where(function (Builder $query) use ($userId) {
                $query->whereNotNull('friends.owner_id')
                    ->orWhere('users.id', '=', $userId);
            });

        if ($searchText != '') {
            $builder = $builder->addScope(new SearchScope($searchText, ['user_name', 'full_name']));
        }

        if ($itemId && $itemType) {
            $taggedQuery = DB::table('friend_tag_friends')
                ->select('owner_id')
                ->where('friend_tag_friends.item_id', $itemId)
                ->where('friend_tag_friends.item_type', $itemType);
            
            $builder->whereNotIn('users.id', $taggedQuery);
        }

        $privacyQuery = DB::table('user_privacy_values as can_be_tagged')
            ->select('user_id')
            ->where('can_be_tagged.name', '=', 'user:can_i_be_tagged')
            ->where('can_be_tagged.privacy', '=', MetaFoxPrivacy::ONLY_ME);

        $builder->whereNotIn('users.id', $privacyQuery);
        $builder->orderByDesc('friends.id');
    }
}
