<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;

class UpdateSearchForFirstMediaListener
{
    public function __construct(protected PhotoGroupRepositoryInterface $repository)
    {
    }

    public function handle(Model $model, ?string $content, int $rest): ?bool
    {
        if (!$model instanceof PhotoGroup) {
            return null;
        }

        $this->repository->updateGlobalSearchForSingleMedia($model, $content, $rest);

        return true;
    }
}
