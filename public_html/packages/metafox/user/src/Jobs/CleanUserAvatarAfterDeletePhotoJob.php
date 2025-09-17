<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\UserProfileRepositoryInterface;

class CleanUserAvatarAfterDeletePhotoJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Content $photo;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function __construct(Content $photo)
    {
        parent::__construct();
        $this->photo = $photo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $query = $this->profileRepository()
            ->getModel()
            ->newQuery()
            ->where('avatar_id', $this->photo->entityId())
            ->where('avatar_type', $this->photo->entityType());

        foreach ($query->cursor() as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $user->update([
                'avatar_id'      => 0,
                'avatar_file_id' => 0,
            ]);
        }
    }

    protected function profileRepository(): UserProfileRepositoryInterface
    {
        return resolve(UserProfileRepositoryInterface::class);
    }
}
