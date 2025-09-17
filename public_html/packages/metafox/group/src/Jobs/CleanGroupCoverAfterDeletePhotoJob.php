<?php

namespace MetaFox\Group\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Jobs\AbstractJob;

class CleanGroupCoverAfterDeletePhotoJob extends AbstractJob implements ShouldBeUnique
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
        $query = $this->groupRepository()
            ->getModel()
            ->newQuery()
            ->where('cover_id', $this->photo->entityId())
            ->where('cover_type', $this->photo->entityType());

        foreach ($query->cursor() as $group) {
            if (!$group instanceof Group) {
                continue;
            }

            $group->updateQuietly([
                'cover_id'             => 0,
                'cover_file_id'        => 0,
                'cover_photo_position' => 0,
            ]);
        }
    }

    protected function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }
}
