<?php

namespace MetaFox\Page\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Notifications\ApproveNewPostNotification;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class SendFollowerNotification extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $model;
    protected Page  $resource;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model, Page $resource)
    {
        parent::__construct();
        $this->model    = $model;
        $this->resource = $resource;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->model instanceof Content) {
            return;
        }

        $item         = $this->model;
        $userItem     = $item->user;
        $resource     = $this->resource;
        $notification = new ApproveNewPostNotification($item);
        $followers    = $exceptUsers = [];

        $builder = app('events')->dispatch('follow.get_builder_follower', [$resource], true);

        $contractUsers = match (!$builder instanceof Builder) {
            true    => $this->handleMembers($resource->entityId()),
            default => $this->handleUsers($builder)
        };

        if (method_exists($item, 'toFollowerNotification')) {
            $exceptUsers = Arr::get($item->toFollowerNotification(), 'exclude', []);
            $exceptUsers = collect($exceptUsers)->pluck('id')->toArray();
        }

        /** @var User $user */
        foreach ($contractUsers as $user) {
            if ($user->entityId() == $userItem->entityId()) {
                continue;
            }

            if (!empty($exceptUsers) && in_array($user->entityId(), $exceptUsers)) {
                continue;
            }

            $followers[] = $user;
        }

        $notificationParams = [$followers, $notification];
        Notification::send(...$notificationParams);
    }

    protected function handleMembers(int $pageId): array
    {
        $contractUsers = [];
        $members       = $this->memberRepository()->getPageMembers($pageId);

        foreach ($members as $member) {
            $contractUsers[] = $member->user;
        }

        return $contractUsers;
    }

    protected function handleUsers(Builder $builder): Collection
    {
        return $this->userRepository()->getModel()->newQuery()->whereIn('id', $builder)->get();
    }

    protected function memberRepository(): PageMemberRepositoryInterface
    {
        return resolve(PageMemberRepositoryInterface::class);
    }

    protected function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }
}
