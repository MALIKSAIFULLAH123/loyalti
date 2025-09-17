<?php

namespace MetaFox\User\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;
use MetaFox\Platform\Contracts\User as UserContract;
use MetaFox\User\Models\CancelFeedback as Model;

/**
 * Interface CancelFeedback.
 * @mixin BaseRepository
 */
interface CancelFeedbackAdminRepositoryInterface
{
    /**
     * @param  UserContract         $context
     * @param  array<string, mixed> $attributes
     * @return Model
     */
    public function createFeedback(UserContract $context, array $attributes): Model;

    /**
     * @param  UserContract         $context
     * @param  array<string, mixed> $attributes
     * @return Builder
     */
    public function viewFeedbacks(UserContract $context, array $attributes = []): Builder;
}
