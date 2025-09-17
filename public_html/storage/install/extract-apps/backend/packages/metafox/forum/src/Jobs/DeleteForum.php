<?php

namespace MetaFox\Forum\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;
use MetaFox\Forum\Support\ForumSupport;
use MetaFox\Platform\Jobs\AbstractJob;

class DeleteForum extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var bool
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(protected int $id, protected string $deleteOption, protected ?int $alternativeId = null)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $forum = Forum::query()
            ->with(['threads', 'subForums'])
            ->withTrashed()
            ->find($this->id);

        $permanentlyDelete = $this->isPermanentlyDelete($forum);

        if ($permanentlyDelete) {
            $this->permanentlyDelete($forum);
            $this->decreaseParentTotal($forum, 'total_sub', $forum->total_sub + 1);

            return;
        }

        $this->decreaseParentTotal($forum, 'total_sub', $forum->total_sub + 1);

        $this->migrationDelete($forum);

        $forum->forceDelete();
    }

    protected function decreaseParentTotal(Forum $forum, string $column, int $total = 1): void
    {
        if (!$forum->parent_id) {
            return;
        }

        resolve(ForumRepositoryInterface::class)->decreaseTotal($forum->parent_id, $column, $total);
    }

    protected function increaseTotal(Forum $forum, string $column, int $total = 1): void
    {
        resolve(ForumRepositoryInterface::class)->increaseTotal($forum->entityId(), $column, $total);
    }

    protected function migrationDelete(Forum $forum): void
    {
        $alternativeForum = Forum::query()
            ->where('id', '=', $this->alternativeId)
            ->first();

        /*
         * In case alternative forum already deleted
         */
        if (null === $alternativeForum) {
            $alternativeForum = Forum::query()
                ->where('parent_id', '=', 0)
                ->orderBy('id')
                ->first();

            if (null === $alternativeForum) {
                return;
            }
        }

        $this->migrateThreads($forum, $alternativeForum);

        $this->migrateSubForums($forum, $alternativeForum);
    }

    protected function migrateSubForums(Forum $forum, Forum $alternativeForum): void
    {
        $count = $forum->subForums->count();

        if (!$count) {
            return;
        }

        $forum->subForums()->update(['parent_id' => $alternativeForum->entityId()]);

        $this->increaseTotal($alternativeForum, 'total_sub', $forum->total_sub);

        $this->migrateLevels($forum->subForums, $alternativeForum->level + 1);
    }

    protected function migrateThreads(Forum $forum, Forum $alternativeForum): void
    {
        if (!$forum->threads->count()) {
            return;
        }

        $totalFailed        = 0;
        $totalFailedComment = 0;

        foreach ($forum->threads as $thread) {
            if (!$thread->update(['forum_id' => $alternativeForum->entityId()])) {
                $totalFailed++;
                $totalFailedComment += $thread->total_comment;
            }
        }

        if ($forum->total_thread) {
            $totalThread = $forum->total_thread - $totalFailed;

            if ($totalThread > 0) {
                $this->increaseTotal($alternativeForum, 'total_thread', $totalThread);
                $this->decreaseParentTotal($forum, 'total_thread', $totalThread);
            }
        }

        if ($forum->total_comment) {
            $totalComment = $forum->total_comment - $totalFailedComment;

            if ($totalComment > 0) {
                $this->increaseTotal($alternativeForum, 'total_comment', $totalComment);
                $this->decreaseParentTotal($forum, 'total_comment', $totalComment);
            }
        }
    }

    protected function migrateLevels(Collection $subForums, int $level): void
    {
        if (!$subForums->count()) {
            return;
        }

        Forum::query()
            ->whereIn('id', $subForums->pluck('id')->toArray())
            ->update(['level' => $level]);

        $subForums->each(function ($sub) {
            if ($sub->subForums->count()) {
                $this->migrateLevels($sub->subForums, $sub->level + 1);
            }
        });
    }

    protected function isPermanentlyDelete(Forum $forum): bool
    {
        if ($this->deleteOption == ForumSupport::DELETE_PERMANENTLY) {
            return true;
        }

        if ($forum->threads->count() == 0 && $forum->subForums->count() == 0) {
            return true;
        }

        return false;
    }

    protected function permanentlyDelete(Forum $forum): void
    {
        $forum->threads->each(function ($thread) {
            $thread->delete();
        });

        $forum->subForums->each(function ($sub) {
            $this->permanentlyDelete($sub);
        });

        $forum->forceDelete();
    }
}
