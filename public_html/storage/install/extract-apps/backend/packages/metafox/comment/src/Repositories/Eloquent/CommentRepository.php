<?php

namespace MetaFox\Comment\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MetaFox\Comment\Models\Comment;
use MetaFox\Comment\Models\CommentAttachment;
use MetaFox\Comment\Models\CommentHistory;
use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Comment\Repositories\CommentHistoryRepositoryInterface;
use MetaFox\Comment\Repositories\CommentRepositoryInterface;
use MetaFox\Comment\Support\FriendScope;
use MetaFox\Comment\Support\Helper;
use MetaFox\Comment\Support\HiddenScope;
use MetaFox\Comment\Support\LimitScope;
use MetaFox\Comment\Support\PendingScope;
use MetaFox\Core\Constants;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;

/**
 * Class CommentRepository.
 * @method Comment getModel()
 * @method Comment find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD)
 */
class CommentRepository extends AbstractRepository implements CommentRepositoryInterface
{
    use HasApprove;
    use HasFilterTagUserTrait;
    use CollectTotalItemStatTrait;

    public const REGEX_LINK = '/(https?:\/\/){1}([\w\-]+(\.[\w\-]+)+|([\w\-]+(\.[\w\-]+)+))([^\s]*[\w@?^=%&\/~+#-])?/';

    public const NEWEST_SORT_OPERATOR = '>';

    public const OLDEST_SORT_OPERATOR = '<';

    public function model(): string
    {
        return Comment::class;
    }

    protected function commentHistoryRepository(): CommentHistoryRepositoryInterface
    {
        return resolve(CommentHistoryRepositoryInterface::class);
    }

    public function viewComments(User $context, array $attributes): Collection
    {
        $sort = $attributes['sort'] ?? 'created_at';

        $sortType = $attributes['sort_type'] ?? 'desc';

        $limit = $attributes['limit'] ?? Pagination::DEFAULT_ITEM_PER_PAGE;

        $itemType = $attributes['item_type'] ?? null;

        $itemId = $attributes['item_id'];

        $parentId = Arr::get($attributes, 'parent_id', 0);

        $comment = new Comment([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ]);

        /*
         * Compatible with old mobile version that has not been followed to new setting
         */
        if ($parentId && MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.9', '<')) {
            $sortType = Helper::getDefaultReplySortType();

            Arr::set($attributes, 'sort_type', $sortType);
        }

        $sortScope = new SortScope();

        $sortScope
            ->setSort($sort)
            ->setSortType($sortType);

        $item = $comment->item;

        policy_authorize(CommentPolicy::class, 'viewAny', $context, $item->owner);

        if (null == $item) {
            throw (new ModelNotFoundException())->setModel($itemType);
        }

        $query = $this->buildCommentsQuery($context, $attributes);

        $blockedScope = new BlockedScope();

        $blockedScope->setContextId($context->entityId())
            ->setPrimaryKey('user_id')
            ->setTable('comments');

        $query->with(['commentAttachment'])
            ->addScope($sortScope);

        return $query
            ->addScope($blockedScope)
            ->orderByRaw($this->buildOrderFriend($context))
            ->limit($limit)
            ->get(['comments.*']);
    }

    protected function buildOrderFriend(User $context): string
    {
        $order = "WHEN comments.user_id <> {$context->entityId()} THEN 2 ELSE 3";

        if (app_active('metafox/friend')) {
            $order = 'WHEN fr.id IS NOT NULL THEN 1 ' . $order;
        }

        return DB::raw(sprintf('CASE %s END ASC', $order))->getValue(DB::getQueryGrammar());
    }

    public function viewComment(User $context, int $id): Comment
    {
        $comment = $this->with(['userEntity', 'commentAttachment'])->find($id);

        policy_authorize(CommentPolicy::class, 'view', $context, $comment);

        return $comment;
    }

    public function checkSpam(User $user, int $commentId, string $content, int $stickerId, bool $hasPhotoId): bool
    {
        $checkComment      = Settings::get('comment.enable_hash_check');
        $totalCommentCheck = Settings::get('comment.comments_to_check');
        $totalMinutes      = Settings::get('comment.total_minutes_to_wait_for_comments', 0);

        if ($checkComment && $totalCommentCheck && $totalMinutes) {
            $date = Carbon::now('UTC')->subMinutes($totalMinutes)->toDateTimeString();

            $query = $this->getModel()->newQuery()
                ->with(['commentAttachment'])
                ->where('user_id', '=', $user->entityId())
                ->where('user_type', '=', $user->entityType())
                ->where('updated_at', '>=', $date)
                ->orderByDesc('updated_at')
                ->limit($totalCommentCheck);

            if ($commentId > 0) {
                $query->where('id', '<>', $commentId);
            }

            $comments = $query->get();

            foreach ($comments as $comment) {
                /** @var Comment $comment */
                $commentAttachment = $comment->commentAttachment;
                if ($stickerId > 0) {
                    if (null != $commentAttachment && $commentAttachment->item_type == CommentAttachment::TYPE_STICKER) {
                        if ($content == $comment->text_parsed && $stickerId == $commentAttachment->item_id) {
                            return true;
                        }
                    }
                }

                if ($stickerId == 0) {
                    if (
                        (null == $commentAttachment && !$hasPhotoId)
                        || (null != $commentAttachment && $commentAttachment->item_type == CommentAttachment::TYPE_LINK)
                    ) {
                        if ($content == $comment->text_parsed) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function createComment(User $context, array $attributes): Comment
    {
        $comment       = (new Comment())->fill($attributes);
        $item          = $comment->item;
        $owner         = $context;
        $taggedFriends = Arr::get($attributes, 'tagged_friends');
        $stickerId     = $attributes['sticker_id'] ?? 0;
        $hasPhotoId    = !empty($attributes['photo_id']);
        $enableThread  = Settings::get('comment.enable_thread');
        $phrase        = null;
        if (null == $item) {
            abort(404, __p('core::phrase.this_post_is_no_longer_available'));
        }

        policy_authorize(CommentPolicy::class, 'create', $context, $item);

        if ($item instanceof Content) {
            $owner = $item->owner;
        }

        /*
         * Filter mention friends, public/closed groups, public pages
         */
        if (is_array($taggedFriends) && count($taggedFriends)) {
            $extra = $this->transformTaggedFriends($context, $context, $owner, $taggedFriends, $attributes['text'], null, false);

            if (count($extra)) {
                $attributes = array_merge($attributes, [
                    'tagged_friends' => Arr::get($extra, 'tagged_friends'),
                    'text'           => Arr::get($extra, 'content'),
                ]);

                $taggedFriends = $attributes['tagged_friends'];
            }
        }

        $textParsed = parse_output()->parse($attributes['text']);

        $isSpam = $this->checkSpam($context, 0, $textParsed, $stickerId, $hasPhotoId);

        policy_authorize(CommentPolicy::class, 'addAttachment', $context, $attributes);

        if ($isSpam) {
            abort(400, __p('core::phrase.you_have_already_added_this_recently_try_adding_something_else'));
        }

        if (array_key_exists('parent_id', $attributes) && $attributes['parent_id'] > 0) {
            $checkParentExists = Comment::query()->where([
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
                'id'        => $attributes['parent_id'],
            ])->exists();

            if (!$checkParentExists) {
                abort(400, __p('comment::phrase.this_comment_has_been_deleted'));
            }

            if (!$enableThread) {
                abort(400, __p('comment::phrase.this_may_because_technical_error'));
            }
        }

        $comment->fill([
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
            'owner_id'        => $item->userId(),
            'owner_type'      => $item->userType(),
            'is_approved'     => $this->handleIsApproved($context, $item),
            'text_parsed'     => $textParsed,
            'tagged_user_ids' => collect($taggedFriends)->pluck('friend_id')->toArray(),
        ])->save();

        $isCheckLink = $canCreateSticker = true;

        if (Arr::has($attributes, 'photo_id') && $attributes['photo_id'] > 0) {
            $isCheckLink = $canCreateSticker = false;
            $phrase      = CommentHistory::PHRASE_ADDED_PHOTO;
            $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_FILE, $attributes['photo_id']);
        }

        if (Arr::has($attributes, 'sticker_id') && $attributes['sticker_id'] > 0 && $canCreateSticker) {
            $isCheckLink = false;
            $phrase      = CommentHistory::PHRASE_ADDED_STICKER;
            $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_STICKER, $attributes['sticker_id']);

            // send signal to sticker to update sticker recent
            app('events')->dispatch('sticker.create_sticker_recent', [$context, $attributes['sticker_id']]);
        }

        if (Arr::has($attributes, 'giphy_gif_id')) {
            $isCheckLink = false;
            $phrase      = CommentHistory::PHRASE_ADDED_GIF;

            $gifData = app('events')->dispatch('giphy.get_gif_data', [$context, $attributes['giphy_gif_id']], true);

            if (!empty($gifData)) {
                $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_GIF, 0, '', $gifData);
            }
        }

        if ($isCheckLink) {
            $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_LINK);
        }

        if (is_array($taggedFriends) && count($taggedFriends)) {
            app('events')->dispatch(
                'friend.create_tag_friends',
                [$context, $comment, $taggedFriends, $comment->entityType()],
                true
            );
        }

        app('events')->dispatch('hashtag.create_hashtag', [$context, $comment, $comment->text_parsed], true);
        app('events')->dispatch('activity.feed.update_latest_activity_time', [$comment->item]);

        $this->commentHistoryRepository()->createHistory($comment->user, $comment, $phrase);

        return $comment->refresh();
    }

    public function updateComment(User $context, int $id, array $attributes): Comment
    {
        $comment = $this->with(['commentAttachment', 'tagData'])->find($id);

        policy_authorize(CommentPolicy::class, 'update', $context, $comment);

        policy_authorize(CommentPolicy::class, 'updateAttachment', $context, $comment, $attributes);

        if (array_key_exists('text', $attributes)) {
            $this->validateText($comment, $attributes);

            $attributes['text_parsed'] = $attributes['text'] != '' ? $comment->text_parsed : '';

            if ($attributes['text'] != $comment->text) {
                $attributes['text_parsed'] = parse_output()->parse($attributes['text']);

                /**@deprecated version == v5.1.8 => remove this */
                if (!$this->commentHistoryRepository()->checkExists($comment)) {
                    $this->commentHistoryRepository()->createHistory($comment->user, $comment);
                }

                app('events')->dispatch('translation.clear_translated_text', [$comment], true);
            }

            $stickerId         = !empty($attributes['sticker_id']) ? $attributes['sticker_id'] : 0;
            $hasPhotoId        = !empty($attributes['photo_id']);
            $commentAttachment = $comment->commentAttachment;

            if (null != $commentAttachment) {
                if (
                    !$stickerId && !array_key_exists('sticker_id', $attributes)
                    && $commentAttachment->item_type == CommentAttachment::TYPE_STICKER
                ) {
                    $stickerId = $commentAttachment->item_id;
                }

                if (
                    !$hasPhotoId && !array_key_exists('photo_id', $attributes)
                    && $commentAttachment->item_type == CommentAttachment::TYPE_FILE
                ) {
                    $hasPhotoId = true;
                }
            }

            $isSpam = $this->checkSpam(
                $context,
                $comment->entityId(),
                $attributes['text_parsed'],
                $stickerId,
                $hasPhotoId
            );

            if ($isSpam) {
                abort(400, __p('core::phrase.you_have_already_added_this_recently_try_adding_something_else'));
            }
        }

        $attributes['tagged_user_ids'] = [];

        $taggedFriends = Arr::get($attributes, 'tagged_friends', []);

        if (is_array($taggedFriends) && count($taggedFriends)) {
            $attributes['tagged_user_ids'] = collect($taggedFriends)->keys();
        }

        $oldText    = $comment->text;
        $oldHashTag = implode(',', parse_output()->getHashtags($comment->text_parsed));
        $comment->fill($attributes)->update();
        $comment->refresh();

        $canUpdateSticker = true;
        $canDeletePhoto   = !(isset($attributes['sticker_id']) && $attributes['sticker_id'] > 0); //for update Attachment. Not delete record

        $phrase = null;
        if (isset($attributes['photo_id'])) {
            if ($attributes['photo_id'] > 0) {
                $canUpdateSticker = false;
                $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_FILE, $attributes['photo_id']);
                $phrase = CommentHistory::PHRASE_ADDED_PHOTO;
            }

            if ($attributes['photo_id'] == 0 && $canDeletePhoto) {
                $this->handleDeleteAttachment($comment, CommentAttachment::TYPE_FILE);
                $phrase = CommentHistory::COMMENT_DELETED_PHOTO;
            }
        }

        if (isset($attributes['sticker_id'])) {
            if ($attributes['sticker_id'] > 0 && $canUpdateSticker) {
                $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_STICKER, $attributes['sticker_id']);
                $phrase = CommentHistory::PHRASE_ADDED_STICKER;
            }

            if ($attributes['sticker_id'] == 0) {
                $this->handleDeleteAttachment($comment, CommentAttachment::TYPE_STICKER);
                $phrase = CommentHistory::PHRASE_DELETED_STICKER;
            }
        }

        if (isset($attributes['giphy_gif_id'])) {
            if ($attributes['giphy_gif_id'] === 0) {
                $this->handleDeleteAttachment($comment, CommentAttachment::TYPE_GIF);
                $phrase = CommentHistory::PHRASE_DELETED_GIF;
            } elseif ($attributes['giphy_gif_id'] != '') {
                $gifData = app('events')->dispatch('giphy.get_gif_data', [$context, $attributes['giphy_gif_id']], true);

                if (!empty($gifData)) {
                    $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_GIF, 0, $oldText, $gifData);
                    $phrase = CommentHistory::PHRASE_ADDED_GIF;
                }
            }
        }

        $comment->refresh();

        if (null == $comment->commentAttachment) {
            $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_LINK);
        }

        if (null != $comment->commentAttachment) {
            if ($comment->commentAttachment->item_type == CommentAttachment::TYPE_LINK) {
                if (!preg_match(self::REGEX_LINK, $comment->text)) {
                    $this->handleDeleteAttachment($comment, CommentAttachment::TYPE_LINK);
                }

                $this->handleCreateAttachment($context, $comment, CommentAttachment::TYPE_LINK, 0, $oldText);
            }
        }

        if (isset($attributes['tagged_friends'])) {
            app('events')->dispatch(
                'friend.update_tag_friends',
                [$context, $comment, $attributes['tagged_friends'], $comment->entityType()],
                true
            );
        }

        $newHashTag = implode(',', parse_output()->getHashtags($comment->text_parsed));
        if (!empty($newHashTag)) {
            if ($newHashTag != $oldHashTag) {
                app('events')->dispatch('hashtag.create_hashtag', [$context, $comment, $comment->text_parsed], true);
            }
        }

        if (empty($newHashTag) && !empty($oldHashTag)) {
            $comment->tagData()->sync([]);
        }

        if (
            $this->commentHistoryRepository()->checkExists($comment)
            && $oldText != $comment->text || $phrase != null
        ) {
            $this->commentHistoryRepository()->createHistory($context, $comment, $phrase);
        }

        return $comment->refresh();
    }

    /**
     * @param User    $context
     * @param Comment $comment
     * @param string  $itemType
     * @param int     $itemId
     * @param string  $oldText
     * @param array   $extraParams
     */
    private function handleCreateAttachment(
        User $context,
        Comment $comment,
        string $itemType,
        int $itemId = 0,
        string $oldText = '',
        array $extraParams = [],
    ): void {
        $attachmentData    = [];
        $commentAttachment = $comment->commentAttachment;

        // single comment should morph to other comment instead of create new comment attachment.
        switch ($itemType) {
            case CommentAttachment::TYPE_FILE:
                $tempFile = upload()->getFile($itemId);

                $attachmentData = [
                    'item_id'   => $tempFile->id,
                    'item_type' => $tempFile->entityType(),
                    'params'    => null,
                ];

                $tempFile->rollUp();
                break;
            case CommentAttachment::TYPE_STICKER:
                $attachmentData = [
                    'item_id'   => $itemId,
                    'item_type' => CommentAttachment::TYPE_STICKER,
                    'params'    => null,
                ];
                break;
            case CommentAttachment::TYPE_LINK:
                if ($oldText != $comment->text) {
                    if (preg_match(self::REGEX_LINK, $comment->text, $matches)) {
                        if (null != $commentAttachment && CommentAttachment::TYPE_LINK == $commentAttachment->item_type) {
                            if (preg_match(self::REGEX_LINK, $oldText, $oldMatches)) {
                                if ($matches[0] == $oldMatches[0]) {
                                    break;
                                }
                            }
                        }

                        try {
                            $params  = [];
                            $ownerId = $comment->item?->owner?->entityId();

                            if ($ownerId) {
                                $params['owner_id'] = $ownerId;
                            }
                            $url  = htmlspecialchars_decode($matches[0]);
                            $data = app('events')->dispatch('core.parse_url', [$url, $context, $params], true);

                            unset($data['resource_name']);

                            $data['actual_link'] = $data['link'] ?? null;
                            $attachmentData      = [
                                'item_id'    => 0,
                                'item_type'  => CommentAttachment::TYPE_LINK,
                                'image_path' => null,
                                'server_id'  => 'public',
                                'params'     => json_encode($data),
                            ];
                        } catch (Exception $e) {
                        }
                    }
                }

                break;
            case CommentAttachment::TYPE_GIF:
                $attachmentData = [
                    'item_id'   => $itemId,
                    'item_type' => CommentAttachment::TYPE_GIF,
                    'params'    => json_encode($extraParams),
                ];
                break;
        }

        if (null != $commentAttachment) {
            if (!empty($attachmentData)) {
                $commentAttachment->update(Arr::except($attachmentData, ['id']));
            }

            return;
        }

        if (!empty($attachmentData)) {
            // fix may be insert id to pgsql prevent sequence nextval
            $comment->commentAttachment()->create(Arr::except($attachmentData, ['id']));
        }
    }

    /**
     * @param Comment $comment
     * @param string  $itemType
     */
    private function handleDeleteAttachment(Comment $comment, string $itemType): void
    {
        $commentAttachment = $comment->commentAttachment;

        if (null != $commentAttachment && $commentAttachment->item_type == $itemType) {
            $commentAttachment->delete();

            if ($itemType == CommentAttachment::TYPE_FILE) {
                app('storage')->deleteAll($commentAttachment->item_id);
            }
        }
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function deleteCommentById(User $context, int $id): array
    {
        $comment = $this->find($id);

        policy_authorize(CommentPolicy::class, 'delete', $context, $comment);

        $item = $comment->item;
        $comment->delete();
        $feedId = null;

        $item?->refresh();

        if ($item instanceof ActivityFeedSource) {
            try {
                $feedAction = $item->toActivityFeed();
                $typeId     = $feedAction?->getTypeId();
                /** @var Content $feed */
                $feed   = app('events')->dispatch('activity.get_feed', [$context, $item, $typeId], true);
                $feedId = $feed?->entityId();
            } catch (Exception $e) {
                //Do nothing
            }
        }

        [, , , $packageId] = app('core.drivers')->loadDriver(Constants::DRIVER_TYPE_ENTITY, $item->entityType());

        $data = [
            'id'             => $comment->entityId(),
            'feed_id'        => $feedId,
            'item_module_id' => PackageManager::getAlias($packageId),
            'item_id'        => $item->entityId(),
            'item_type'      => $item->entityType(),
            'statistic'      => [
                'total_comment' => $item instanceof HasTotalComment ? $item->total_comment : 0,
            ],
        ];

        $extra = app('events')->dispatch('core.proxy_item', [$item], true);

        if (is_array($extra)) {
            $data = array_merge($data, $extra);
        }

        return $data;
    }

    public function deleteCommentByParentId(int $parentId): bool
    {
        return $this->getModel()->where('parent_id', $parentId)->each(function (Comment $comment) {
            $comment->delete();
        });
    }

    public function getRelatedCommentsByTypeQuery(
        User $context,
        string $itemType,
        int $itemId,
        array $attributes = []
    ): Builder {
        $numberOfCommentOnFeed = Settings::get('comment.prefetch_comments_on_feed');
        $excludedIds           = Arr::get($attributes, 'excluded_ids');
        $orderedId             = Arr::get($attributes, 'ordered_id');

        $relations = [
            'commentAttachment',
            'parentComment',
            'parentComment.commentAttachment',
            'parentComment.userEntity',
        ];

        $query = $this->getModel()->newQuery()
            ->with($relations)
            ->select('comments.*');

        $query->addScope(new PendingScope($context));

        $query->addScope(new HiddenScope($context));

        if (is_array($excludedIds) && count($excludedIds)) {
            $query->whereNotIn('comments.id', $excludedIds);
        }

        if (is_numeric($orderedId) && $orderedId > 0) {
            $query->orderByRaw("
                CASE
                    WHEN comments.id = {$orderedId} THEN 1
                    ELSE 2
                END ASC
            ");
        }

        $limitScope = new LimitScope();

        $limitScope->setLimit($numberOfCommentOnFeed);

        $query->addScope($limitScope);

        $blockedScope = new BlockedScope();

        $blockedScope->setContextId($context->entityId())
            ->setPrimaryKey('user_id')
            ->setTable('comments');

        $query->addScope($blockedScope)
            ->where([
                'comments.item_id'   => $itemId,
                'comments.item_type' => $itemType,
                'comments.parent_id' => 0,
            ]);

        $this->buildSortingForRelatedCommentsByType($query, $context, $attributes);

        return $query;
    }

    protected function buildSortingForRelatedCommentsByType(Builder $builder, User $context, array $attributes = []): void
    {
        $sortType = Arr::get(
            $attributes,
            'sort_type',
            Helper::getSortType(Settings::get('comment.sort_by', Helper::SORT_ALL))
        );

        $builder->addScope(new FriendScope($context));

        $builder->orderBy('comments.id', $sortType)
            ->orderByRaw($this->buildOrderFriend($context));
    }

    public function getReducerSortingBuilder(Builder $builder, User $context, array $attributes = []): Builder
    {
        $newBuilder = $this->getModel()->newQuery()
            ->from($builder, 'comments')
            ->select('comments.*');

        $this->buildSortingForRelatedCommentsByType($newBuilder, $context, $attributes);

        return $newBuilder;
    }

    public function getRelatedCommentsByType(
        User $context,
        string $itemType,
        int $itemId,
        array $attributes = []
    ): Collection {
        $limitReplies = Settings::get('comment.prefetch_replies_on_feed');

        $comments = $this->getRelatedCommentsByTypeQuery($context, $itemType, $itemId, $attributes)->get();

        /* @var Collection|Comment[] $comments */
        return $this->mappingComment($context, $comments, $limitReplies);
    }

    public function getRelatedComments(User $context, HasTotalComment $content): Collection
    {
        $itemId = $content->entityId();

        $itemType = $content->entityType();

        return $this->getRelatedCommentsByType($context, $itemType, $itemId);
    }

    public function getRelatedCommentForItemDetailQuery(User $context, mixed $itemType, mixed $itemId, array $attributes = []): Builder
    {
        $limit     = Settings::get('comment.prefetch_comments_on_feed');
        $orderedId = Arr::get($attributes, 'ordered_id');
        $sortType  = Arr::get($attributes, 'sort_type');

        $query = $this->getModel()->newQuery()
            ->select('comments.*')
            ->where([
                'comments.parent_id' => 0,
                'comments.item_id'   => $itemId,
                'comments.item_type' => $itemType,
            ]);

        $query->addScope(new PendingScope($context));

        if (is_numeric($orderedId) && $orderedId > 0) {
            $query->orderByRaw("
                CASE
                    WHEN comments.id = {$orderedId} THEN 1
                    ELSE 2
                END ASC
            ");
        }

        $query->addScope(new HiddenScope($context));

        if (null === $sortType) {
            $sortType = Helper::getSortType(Settings::get('comment.sort_by', Helper::SORT_ALL));
        }

        $query->addScope(new FriendScope($context));

        $limitScope = new LimitScope();

        $limitScope->setLimit($limit);

        $blockedScope = new BlockedScope();

        $blockedScope->setContextId($context->entityId())
            ->setPrimaryKey('user_id')
            ->setTable('comments');

        return $query->orderBy('comments.id', $sortType)
            ->orderByRaw($this->buildOrderFriend($context))
            ->addScope($blockedScope)
            ->addScope($limitScope)
            ->orderByDesc('comments.updated_at');
    }

    public function getRelatedCommentsForItemDetail(User $context, HasTotalComment $content, int $limit = 6, array $attributes = []): Collection
    {
        $limitReplies = Settings::get('comment.prefetch_replies_on_feed');

        $query = $this->getRelatedCommentForItemDetailQuery($context, $content->entityType(), $content->entityId(), $attributes);

        /** @var Collection|Comment[] $comments */
        $comments = $query->get();

        if (!$comments instanceof Collection) {
            return new Collection([]);
        }

        /* @var Collection|Comment[] $comments */
        return $this->mappingComment($context, $comments, $limitReplies);
    }

    protected function mappingComment(User $context, Collection $comments, mixed $limitChildren): Collection
    {
        if (!$comments->count()) {
            return $comments;
        }

        if (!Helper::isShowReply()) {
            return $comments;
        }

        //TODO: Please improve later by using UNION ALL with one query
        /* @var Collection|Comment[] $comments */
        return $comments->map(function (Comment $comment) use ($limitChildren, $context) {
            return $comment->setRelation('children', $this->getReplies($comment, $context, $limitChildren));
        });
    }

    public function getReplies(Comment $comment, User $context, mixed $limitChildren): Collection
    {
        $limitScope = new LimitScope();

        $limitScope->setLimit($limitChildren);

        return $comment->children()
            ->with(['userEntity', 'commentAttachment'])
            ->orderBy('id', Helper::getDefaultReplySortType())
            ->orderByDesc('id')
            ->addScope($limitScope)
            ->addScope(new PendingScope($context))
            ->get();
    }

    /**
     * @param Comment              $comment
     * @param array<string, mixed> $attributes
     *
     * @throws ValidationException
     */
    private function validateText(Comment $comment, array $attributes): void
    {
        if ($attributes['text'] == '') {
            if (empty($attributes['sticker_id']) && empty($attributes['photo_id'])) {
                $isMissText = false;
                if (null == $comment->commentAttachment) {
                    $isMissText = true;
                }

                if (
                    null != $comment->commentAttachment
                    && (array_key_exists('photo_id', $attributes) || array_key_exists('sticker_id', $attributes))
                ) {
                    $isMissText = true;
                }

                if ($isMissText) {
                    $errorMessage = __p('validation.required_without', ['attribute' => 'text', 'values' => 'photo_id']);
                    if (app_active('metafox/sticker')) {
                        $errorMessage = __p('validation.required_without_all', [
                            'attribute' => 'text',
                            'values'    => implode(' / ', ['photo_id', 'sticker_id']),
                        ]);
                    }

                    throw ValidationException::withMessages([
                        'text' => $errorMessage,
                    ]);
                }
            }
        }
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildCommentsQuery(User $context, array $attributes): Builder
    {
        $itemId = $attributes['item_id'];

        $itemType = $attributes['item_type'];

        $parentId = Arr::get($attributes, 'parent_id', 0);

        $excludes = Arr::get($attributes, 'excludes', []);

        $lastId = Arr::get($attributes, 'last_id', 0);

        $sortType = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);

        $commentId = Arr::get($attributes, 'comment_id');

        $query = $this->getModel()->newQuery();

        $this->buildConditionForViewDetailCommentInMobile($query, $commentId);

        $query->addScope(new FriendScope($context));

        $query->where([
            'comments.item_id'   => $itemId,
            'comments.item_type' => $itemType,
            'comments.parent_id' => $parentId,
        ]);

        $query->addScope(new PendingScope($context));

        $query->addScope(new HiddenScope($context));

        if (is_array($excludes) && count($excludes)) {
            $query->whereNotIn('comments.id', $excludes);
        }

        if ($lastId > 0) {
            $operator = $sortType == Browse::SORT_TYPE_DESC ? self::OLDEST_SORT_OPERATOR : self::NEWEST_SORT_OPERATOR;

            $query->where('comments.id', $operator, $lastId);
        }

        return $query;
    }

    protected function buildConditionForViewDetailCommentInMobile(Builder $query, ?int $commentId): void
    {
        if (!$commentId) {
            return;
        }

        $comment = $this->getModel()->newQuery()
            ->where('id', '=', $commentId)
            ->first();

        if (null === $comment) {
            return;
        }

        $parentCommentId = $comment->entityId();

        if ($comment->parent_id) {
            $parentCommentId = $comment->parent_id;
        }

        $query->orderBy(DB::raw("CASE WHEN comments.id = {$parentCommentId} THEN 1 ELSE 2 END"));
    }

    /**
     * @inheritDoc
     */
    public function getUsersCommentByItem(User $context, array $attributes): array
    {
        $limit = $attributes['limit'];

        $itemType = $attributes['item_type'];

        $itemId = $attributes['item_id'];

        $comment = new Comment([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ]);

        $item = $comment->item;

        if (null == $item) {
            throw (new ModelNotFoundException())->setModel($itemType);
        }

        $userIds = $this->getModel()->newQuery()
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->addScope(new PendingScope($context))
            ->pluck('user_id')->toArray();

        $query = UserEntity::query()
            ->whereIn('id', $userIds);

        $total = $query->count();

        $collection = new Collection();

        if ($total > 0) {
            $collection = $query->limit($limit)->get();
        }

        return [$total, $collection];
    }

    public function getRelevantCommentsById(User $context, int $id, ?Entity $content = null, bool $isFeed = true): ?Collection
    {
        $where = [
            'id' => $id,
        ];

        if (null !== $content) {
            $where = array_merge($where, [
                'item_type' => $content->entityType(),
                'item_id'   => $content->entityId(),
            ]);
        }

        /**
         * @var Comment|null $relevantComment
         */
        $relevantComment = $this
            ->getModel()
            ->newQuery()
            ->with(['parentComment', 'item'])
            ->where($where)
            ->first();

        if (null === $relevantComment) {
            return null;
        }

        $reply = null;

        if ($relevantComment->parent_id > 0) {
            $reply = $relevantComment;

            $relevantComment = $relevantComment->parentComment;
        }

        if (null === $relevantComment) {
            return null;
        }

        if ($isFeed) {
            $collection = $this->getRelatedCommentsByType($context, $relevantComment->itemType(), $relevantComment->itemId(), [
                'ordered_id' => $relevantComment->entityId(),
                'sort_type'  => Browse::SORT_TYPE_ASC,
            ]);

            return $this->getReplyDetail($collection, $reply);
        }

        if (!$content instanceof HasTotalComment) {
            return null;
        }

        $collection = $this->getRelatedCommentsForItemDetail($context, $content, 6, [
            'ordered_id' => $relevantComment->entityId(),
            'sort_type'  => Browse::SORT_TYPE_ASC,
        ]);

        return $this->getReplyDetail($collection, $reply);
    }

    protected function getReplyDetail(Collection $relevantComments, ?Comment $reply = null): Collection
    {
        if (null === $reply) {
            return $relevantComments;
        }

        if (null === $reply->parentComment) {
            return $relevantComments;
        }

        $relevantComments = $relevantComments->keyBy('id');

        /**
         * @var Comment|null $parent
         */
        $parent = $relevantComments->get($reply->parent_id);

        if (null === $parent) {
            return $relevantComments->values();
        }

        $where = [
            'item_type' => $reply->itemType(),
            'item_id'   => $reply->itemId(),
            'parent_id' => $parent->entityId(),
        ];

        $beforeReplies = $this->getModel()->newQuery()
            ->where($where)
            ->where('id', '<', $reply->entityId())
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        $afterReplies = $this->getModel()->newQuery()
            ->where($where)
            ->where('id', '>', $reply->entityId())
            ->orderBy('id')
            ->limit(2)
            ->get();

        $items = [];

        $replyDetailStatistics = [
            'total_previous_remaining' => 0,
            'total_more_remaining'     => 0,
        ];

        if ($beforeReplies->count()) {
            $first = $beforeReplies->last();

            if ($first instanceof Comment) {
                $count = $this->getModel()->newQuery()
                    ->where($where)
                    ->where('id', '<', $first->entityId())
                    ->count();

                Arr::set($replyDetailStatistics, 'total_previous_remaining', $count);
            }

            $replies = $beforeReplies->reverse()->all();

            $items = array_merge($items, $replies);
        }

        $items[] = $reply;

        if ($afterReplies->count()) {
            $last = $afterReplies->last();

            if ($last instanceof Comment) {
                $count = $this->getModel()->newQuery()
                    ->where($where)
                    ->where('id', '>', $last->entityId())
                    ->count();

                Arr::set($replyDetailStatistics, 'total_more_remaining', $count);
            }

            $replies = $afterReplies->all();

            $items = array_merge($items, $replies);
        }

        $parent->relevant_children = $items;

        $parent->reply_detail_statistics = $replyDetailStatistics;

        $relevantComments->offsetSet($parent->entityId(), $parent);

        return $relevantComments->values();
    }

    public function getTotalHidden(User $context, HasTotalComment $item, int $parentId = 0): int
    {
        if ($context->hasPermissionTo('comment.moderate')) {
            return 0;
        }

        if ($context->entityId() == $item->userId()) {
            return 0;
        }

        $query = $this->getModel()->newQuery()
            ->join('comment_hidden', function (JoinClause $joinClause) {
                $joinClause->on('comment_hidden.item_id', '=', 'comments.id')
                    ->where('comment_hidden.type', '=', Helper::HIDE_GLOBAL);
            })
            ->where([
                'comments.item_id'   => $item->entityId(),
                'comments.item_type' => $item->entityType(),
                'comments.parent_id' => $parentId,
            ])
            ->where('comments.user_id', '<>', $context->entityId());

        $query->addScope(new PendingScope($context));

        $total = $query->count();

        // divide 2 because when inserting global scope hidden view, we will insert owner of comment and owner of item
        return $total / 2;
    }

    public function removeLinkPreview(Comment $comment): bool
    {
        if (null === $comment->commentAttachment) {
            return false;
        }

        $params = json_decode($comment->commentAttachment->params, true);

        if (!is_array($params)) {
            $params = [];
        }

        if (Arr::get($params, 'is_hidden')) {
            return false;
        }

        Arr::set($params, 'is_hidden', true);

        $comment->commentAttachment->update(['params' => json_encode($params)]);

        return true;
    }

    protected function handleIsApproved(User $context, ?Content $item): bool
    {
        if ($item && $item->owner instanceof HasPrivacyMember) {
            return true;
        }

        $allow = app('events')->dispatch('comment.allow_approved', [$item], true);

        return !empty($allow) || $context->hasPermissionTo('comment.auto_approved');
    }

    public function viewReplyDetail(Comment $comment): Collection
    {
        $parent = null;

        if ($comment->parent_id && $comment->parentComment instanceof Comment) {
            $parent = $comment->parentComment;
        }

        if (null === $parent) {
            return collect([$comment]);
        }

        return $this->getReplyDetail(collect([$parent]), $comment);
    }

    public function translateComment(Comment $comment, User $context, array $attributes): array|null
    {
        policy_authorize(CommentPolicy::class, 'translate', $context);

        $text = $comment->text_parsed;

        if (null === $text) {
            return null;
        }

        $data = app('events')->dispatch('translation.translate', [$text, $comment, $context, $attributes], true);

        return $data;
    }
}
