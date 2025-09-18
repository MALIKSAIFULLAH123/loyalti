<?php

namespace MetaFox\LiveStreaming\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    /**
     * @param  User|null $context
     * @param  Model     $model
     * @return void
     */
    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof LiveVideo) {
            return;
        }
        $this->getLiveVideoRepository()->publishVideoActivity($model, true);
    }

    protected function getLiveVideoRepository(): LiveVideoRepositoryInterface
    {
        return resolve(LiveVideoRepositoryInterface::class);
    }
}
