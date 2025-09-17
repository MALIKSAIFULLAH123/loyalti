<?php

namespace MetaFox\Hashtag\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Hashtag\Repositories\Eloquent\TagRepository;
use MetaFox\Platform\Contracts\User as ContractUser;

class UpdateTotalItemListener
{
    private TagRepository $repository;

    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  ContractUser           $context
     * @param  array<int>             $ids
     * @return void
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(ContractUser $context, array $ids): void
    {
        foreach ($ids as $id) {
            $this->repository->updateTotalItem($context, $id);
        }
    }
}
