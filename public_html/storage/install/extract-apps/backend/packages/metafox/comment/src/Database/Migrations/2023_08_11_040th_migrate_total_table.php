<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use MetaFox\Platform\Support\DbTableHelper;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /** @var string */
    protected string $modelName = \MetaFox\Blog\Models\Blog::class;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!class_exists($this->modelName)) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $obj */
        $model = resolve($this->modelName);

        $this->migrateTotalReply($model);
        $this->migrateTotalPendingReply($model);
        $this->migrateTotalTagFriends($model);
    }

    public function migrateTotalTagFriends(Model $model)
    {
        if (!class_exists('\MetaFox\Friend\Models\TagFriend')) {
            return;
        }

        $updateColumn = 'total_tag_friend';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        $query = \MetaFox\Friend\Models\TagFriend::query()
            ->selectRaw('item_id, count(*) as aggregate')
            ->from('friend_tag_friends')
            ->join('user_entities', function ($query) {
                $query->on('user_entities.id', '=', 'friend_tag_friends.owner_id')
                    ->on('user_entities.entity_type', '=', 'friend_tag_friends.owner_type');
            })->whereRaw('is_mention=0')
            ->where('item_type', $model::ENTITY_TYPE)
            ->groupBy('item_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $query,
            'id',
            'item_id',
            false
        );
    }

    public function migrateTotalPendingReply(Model $model)
    {
        $updateColumn = 'total_pending_reply';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        if (!class_exists('\MetaFox\Comment\Models\Comment')) {
            return;
        }

        // select for total reply
        $query = MetaFox\Comment\Models\Comment::query()
            ->selectRaw('parent_id, count(*) as aggregate')
            ->whereRaw('parent_id<>0')
            ->whereRaw('is_approved=0')
            ->groupBy('parent_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $query,
            'id',
            'parent_id',
            false
        );
    }

    public function migrateTotalPendingComment(Model $model)
    {
        $updateColumn = 'total_pending_comment';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        if (!class_exists('\MetaFox\Comment\Models\Comment')) {
            return;
        }

        // select for total reply
        $query = MetaFox\Comment\Models\Comment::query()
            ->selectRaw('parent_id, count(*) as aggregate')
            ->where([
                'parent_id'   => 0,
                'is_approved' => 0,
            ])
            ->groupBy('parent_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $query,
            'id',
            'parent_id',
            false
        );
    }

    public function migrateTotalReply(Model $model)
    {
        $updateColumn = 'total_reply';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        // skip migrate router.
        if (!class_exists('\MetaFox\Comment\Models\Comment')) {
            return;
        }

        // select for total reply
        $query = MetaFox\Comment\Models\Comment::query()
            ->selectRaw('parent_id, count(*) as aggregate')
            ->whereRaw('parent_id<>0')
            ->whereRaw('is_approved=1')
            ->groupBy('parent_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $query,
            'id',
            'parent_id',
            false
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
