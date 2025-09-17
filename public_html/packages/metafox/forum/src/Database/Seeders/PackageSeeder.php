<?php

namespace MetaFox\Forum\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use MetaFox\Forum\Models\Forum;
use MetaFox\Forum\Models\Permission;
use MetaFox\Forum\Repositories\Eloquent\ForumRepository;

class PackageSeeder extends Seeder
{
    protected $repository;

    /**
     * @param ForumRepository $repository
     */
    public function __construct(ForumRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array[]
     */
    public function getDefaultForums(): array
    {
        return [
            [
                'title'      => 'forum::phrase.forum_discussions',
                'ordering'   => 1,
                'level'      => 1,
                'sub_forums' => [
                    [
                        'title'    => 'forum::phrase.forum_general',
                        'ordering' => 1,
                        'level'    => 2,
                    ],
                    [
                        'title'    => 'forum::phrase.forum_movies',
                        'ordering' => 2,
                        'level'    => 2,
                    ],
                    [
                        'title'    => 'forum::phrase.forum_music',
                        'ordering' => 3,
                        'level'    => 2,
                    ],
                ],
            ],
            [
                'title'      => 'forum::phrase.forum_computers_technology',
                'ordering'   => 2,
                'level'      => 1,
                'sub_forums' => [
                    [
                        'title'    => 'forum::phrase.forum_computers',
                        'ordering' => 1,
                        'level'    => 2,
                    ],
                    [
                        'title'    => 'forum::phrase.forum_electronics',
                        'ordering' => 2,
                        'level'    => 2,
                    ],
                    [
                        'title'    => 'forum::phrase.forum_gadgets',
                        'ordering' => 3,
                        'level'    => 2,
                    ],
                    [
                        'title'    => 'forum::phrase.forum_general',
                        'ordering' => 4,
                        'level'    => 2,
                    ],
                ],
            ],
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->initializeForums();
        $this->initializePermissions();
    }

    protected function initializePermissions(): void
    {
        $exists = Permission::query()
            ->exists();

        if ($exists) {
            return;
        }

        $perms = [
            [
                'var_name' => 'edit_post',
                'name'     => 'forum::permission.can_edit_posts_label',
            ],
            [
                'var_name' => 'delete_post',
                'name'     => 'forum::permission.can_delete_posts_label',
            ],
            [
                'var_name' => 'post_sticky',
                'name'     => 'forum::permission.can_stick_threads_label',
            ],
            [
                'var_name' => 'move_thread',
                'name'     => 'forum::permission.can_move_threads_label',
            ],
            [
                'var_name' => 'copy_thread',
                'name'     => 'forum::permission.can_copy_threads_label',
            ],
            [
                'var_name' => 'close_thread',
                'name'     => 'forum::permission.can_close_threads_label',
            ],
            [
                'var_name' => 'merge_thread',
                'name'     => 'forum::permission.can_merge_threads_label',
            ],
            [
                'var_name' => 'can_reply',
                'name'     => 'forum::permission.can_reply_to_threads_label',
            ],
            [
                'var_name' => 'add_thread',
                'name'     => 'forum::permission.can_create_forum_thread_label',
            ],
            /*[
                'var_name'   => 'approve_thread',
                'name' => 'forum::permission.can_approve_forum_thread_label',
            ],
            [
                'var_name'   => 'approve_post',
                'name' => 'forum::permission.can_approve_forum_post_label',
            ],*/
        ];

        Permission::query()->upsert($perms, ['var_name'], ['name']);
    }

    protected function initializeForums(): void
    {
        $isExists = $this->repository->getModel()
            ->newQuery()
            ->exists();

        if ($isExists) {
            $this->upgradeForums();

            return;
        }

        $this->installForums();
    }

    protected function installForums(): void
    {
        foreach ($this->getDefaultForums() as $forum) {
            $model = $this->repository->create([
                'title'       => $forum['title'],
                'description' => null,
                'ordering'    => $forum['ordering'],
            ]);

            if ($model instanceof Forum && $model->entityId()) {
                $subForums = Arr::get($forum, 'sub_forums', []);

                foreach ($subForums as $subForum) {
                    $this->repository->create(array_merge($subForum, [
                        'parent_id'   => $model->entityId(),
                        'description' => null,
                    ]));
                }

                if (count($subForums)) {
                    $model->update(['total_sub' => count($subForums)]);
                }
            }
        }
    }

    protected function upgradeForums(): void
    {
        $upgradedForums = Forum::query()
            ->with(['parentForums'])
            ->where('parent_id', '<>', 0)
            ->where('level', '=', 1)
            ->get();

        if (!$upgradedForums->count()) {
            return;
        }

        foreach ($upgradedForums as $upgradedForum) {
            if (null === $upgradedForum->parentForums) {
                $upgradedForum->update(['parent_id' => 0]);
                continue;
            }

            $upgradedForum->update(['level' => (int) $upgradedForum->parentForums->level + 1]);
        }
    }
}
