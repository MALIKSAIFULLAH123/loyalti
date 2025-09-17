<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class PutToStreamsListener
{
    public function __construct(protected FeedRepositoryInterface $repository) { }

    /**
     * @param User|null $context
     * @param User      $friend
     * @param int       $itemId
     * @param string    $itemType
     * @param string    $typeId
     * @param array     $attributes
     * @return void
     * @deprecated v5.2 remove parameter "$itemId, $itemType, $typeId"
     */
    public function handle(?User $context, User $friend, int $itemId, string $itemType, string $typeId, array $attributes): void
    {
        $this->repository->handlePutToTagStream($context, $friend, $itemId, $itemType, $typeId, $attributes);
    }
}
