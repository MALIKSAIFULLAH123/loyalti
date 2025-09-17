<?php

namespace MetaFox\Poll\Listeners;

use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\PollRepositoryInterface;

class PrepareFeedDataListener
{
    /**
     * @var PollRepositoryInterface
     */
    protected $repository;

    public function __construct(PollRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  string $itemType
     * @param  array  $attributes
     * @return array
     */
    public function handle(string $itemType, array $attributes): ?array
    {
        if ($itemType != Poll::ENTITY_TYPE) {
            return null;
        }

        return $this->repository->prepareDataForFeed($attributes);
    }
}
