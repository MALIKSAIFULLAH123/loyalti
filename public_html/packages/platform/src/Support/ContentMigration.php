<?php

namespace MetaFox\Platform\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;

class ContentMigration extends Migration
{
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

        $this->addTotalComment($model);
        $this->addTotalReply($model);
        $this->addTotalPendingComment($model);
        $this->addTotalPendingReply($model);
        $this->addTotalTagFriends($model);
    }

    public function addTotalTagFriends(Model $model)
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
            })
            ->where([
                'is_mention' => 0,
                'item_type'  => $model::ENTITY_TYPE,
            ])
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

    public function addTotalPendingReply(Model $model)
    {
        $updateColumn = 'total_pending_reply';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        if (!class_exists('\MetaFox\Comment\Models\Comment')) {
            return;
        }

        // select for total reply
        $sql = \MetaFox\Comment\Models\Comment::query()
            ->selectRaw('item_id, count(*) as aggregate')
            ->where([
                ['parent_id', '<>', 0],
                'is_approved' => 0,
                'item_type'   => $model::ENTITY_TYPE,
            ])
            ->groupBy('item_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $sql,
            'id',
            'item_id',
            false
        );
    }

    public function addTotalReply(Model $model)
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
        $sql = \MetaFox\Comment\Models\Comment::query()
            ->selectRaw('item_id, count(*) as aggregate')
            ->where([
                ['parent_id', '<>', 0],
                'is_approved' => 1,
                'item_type'   => $model::ENTITY_TYPE,
            ])
            ->groupBy('item_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $sql,
            'id',
            'item_id',
            false
        );
    }

    public function addTotalPendingComment(Model $model)
    {
        $updateColumn = 'total_pending_comment';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        // skip migrate router.
        if (!class_exists('\MetaFox\Comment\Models\Comment')) {
            return;
        }

        // select for total reply
        $sql = \MetaFox\Comment\Models\Comment::query()
            ->selectRaw('item_id, count(*) as aggregate')
            ->where([
                'parent_id'   => 0,
                'is_approved' => 0,
                'item_type'   => $model::ENTITY_TYPE,
            ])
            ->groupBy('item_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $sql,
            'id',
            'item_id',
            false
        );
    }

    public function addTotalComment(Model $model)
    {
        $updateColumn = 'total_comment';

        if (!DbTableHelper::addMisingTotalColumn($model, $updateColumn)) {
            return;
        }

        // skip migrate router.
        if (!class_exists('\MetaFox\Comment\Models\Comment')) {
            return;
        }

        // select for total reply
        $sql = \MetaFox\Comment\Models\Comment::query()
            ->selectRaw('item_id, count(*) as aggregate')
            ->where([
                'parent_id'   => 0,
                'is_approved' => 1,
                'item_type'   => $model::ENTITY_TYPE,
            ])
            ->groupBy('item_id');

        DbTableHelper::migrateCounter(
            $model,
            $updateColumn,
            $sql,
            'id',
            'item_id',
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
}
