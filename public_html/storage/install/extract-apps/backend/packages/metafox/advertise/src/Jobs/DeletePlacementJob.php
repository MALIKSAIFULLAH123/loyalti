<?php

namespace MetaFox\Advertise\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Advertise\Models\Placement;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Jobs\AbstractJob;

class DeletePlacementJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    public function __construct(protected int $placementId, protected string $deleteOption, protected ?int $alternativeId = null)
    {
        parent::__construct();
    }

    public function handle()
    {
        $placement = resolve(PlacementRepositoryInterface::class)
            ->with(['advertises'])
            ->withTrashed()
            ->find($this->placementId);

        if ($this->deleteOption === Support::DELETE_PERMANENTLY) {
            $this->permanentlyDelete($placement);

            return;
        }

        $this->migrationDelete($placement);
    }

    protected function migrationDelete(Placement $placement): void
    {
        $placement->forceDelete();

        $alternativePlacement = Placement::query()
            ->where('id', '=', (int) $this->alternativeId)
            ->first();

        if (null === $alternativePlacement) {
            $this->deleteAdvertises($placement);

            return;
        }

        $placement->advertises->each(function ($advertise) use ($alternativePlacement) {
            $advertise->update(['placement_id' => $alternativePlacement->entityId()]);
        });
    }

    protected function deleteAdvertises(Placement $placement): void
    {
        $placement->advertises->each(function ($advertise) {
            $advertise->delete();
        });
    }

    protected function permanentlyDelete(Placement $placement): void
    {
        $this->deleteAdvertises($placement);

        $placement->forceDelete();
    }
}
