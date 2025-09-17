<?php

namespace MetaFox\LiveStreaming\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Notifications\StartLiveStreamNotification;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Contracts\IsNotifiable;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Models\User;

/**
 * Class DeleteCategoryJob.
 * @ignore
 * @codeCoverageIgnore
 */
class SendStartLiveStreamNotification extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use RepoTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $live_video_id)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $repository = $this->getLiveVideoRepository();

        /** @var LiveVideo $liveVideo */
        $liveVideo = $repository->find($this->live_video_id);
        if (!$liveVideo || !$liveVideo->is_streaming || $liveVideo->privacy == MetaFoxPrivacy::ONLY_ME || !$liveVideo->is_approved) {
            return;
        }

        $friendBuilder = app('events')->dispatch('friend.get_eloquent_builder', [], true);

        if ($friendBuilder instanceof Builder) {
            $friends = $friendBuilder->select('friends.*')
                ->leftJoin('livestreaming_notification_setting AS ns', function (JoinClause $join) use ($liveVideo) {
                    $join->on('ns.user_id', '=', 'friends.owner_id')
                        ->where([
                            ['ns.owner_id', '=', $liveVideo->userId()],
                        ]);
                })
                ->join('livestreaming_live_videos as lv', 'lv.owner_id', '=', 'friends.user_id')
                ->join('livestreaming_privacy_streams as stream', function (JoinClause $join) use ($liveVideo) {
                    $join->on('stream.item_id', '=', 'lv.id')
                        ->where('lv.id', '=', $liveVideo->entityId());
                })
                ->join('core_privacy_members AS member', function (JoinClause $join) {
                    $join->on('stream.privacy_id', '=', 'member.privacy_id')
                        ->on('member.user_id', '=', 'friends.owner_id');
                })
                ->where('friends.user_id', '=', $liveVideo->userId())
                ->whereNotIn('friends.owner_id', $liveVideo->tagged_friends ?? [])
                ->whereNull('ns.user_id')
                ->with(['owner'])
                ->get()
                ->collect();

            $userIdFollowers = null;
            if ($this->activitySub()) {
                $userIdFollowers = $this->activitySub()
                    ->buildSubscriptions(['owner_id' => $liveVideo->ownerId()])
                    ->pluck('user_id')->toArray();
            }
            $users = $friends->pluck('owner')->filter(function (mixed $user) use ($userIdFollowers) {
                return $userIdFollowers === null || in_array($user->entityId(), $userIdFollowers);
            });

            if ($userIdFollowers !== null && count($userIdFollowers)) {
                // Send to followers
                $friendId = [];
                $users->each(function ($user) use (&$friendId) {
                    $friendId[] = $user->id;
                });
                $remainingFollower = array_diff($userIdFollowers, $friendId);
                $followers         = User::query()
                    ->select('users.*')
                    ->leftJoin('livestreaming_notification_setting AS ns', function (JoinClause $join) use ($liveVideo) {
                        $join->on('ns.user_id', '=', 'users.id')
                            ->where([
                                ['ns.owner_id', '=', $liveVideo->userId()],
                            ]);
                    })
                    ->join('livestreaming_privacy_streams as stream', function (JoinClause $join) use ($liveVideo) {
                        $join->where('stream.item_id', '=', $liveVideo->entityId());
                    })
                    ->join('core_privacy_members AS member', function (JoinClause $join) {
                        $join->on('stream.privacy_id', '=', 'member.privacy_id')
                            ->on('member.user_id', '=', 'users.id');
                    })
                    ->whereIn('users.id', $remainingFollower)
                    ->where('users.id', '!=', $liveVideo->userId())
                    ->whereNull('ns.user_id')
                    ->get();
                foreach ($followers as $follower) {
                    $users->add($follower);
                }
            }
            $notifiable = $users->filter(function (mixed $user) {
                return $user instanceof IsNotifiable;
            })->values();
            Notification::sendNow($notifiable, new StartLiveStreamNotification($liveVideo));
        }
    }

    private function activitySub()
    {
        return resolve('Activity.Subscription');
    }
}
