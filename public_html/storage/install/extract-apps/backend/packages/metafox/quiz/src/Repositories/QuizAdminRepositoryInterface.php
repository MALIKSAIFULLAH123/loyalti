<?php

namespace MetaFox\Quiz\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface QuizAdminRepositoryInterface.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 */
interface QuizAdminRepositoryInterface extends HasSponsor, HasSponsorInFeed
{

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     * @return Builder
     */
    public function viewQuizzes(User $context, array $attributes): Builder;

    /**
     * @param User $context
     * @param int  $id
     * @return Content
     */
    public function approve(User $context, int $id): Content;
}
