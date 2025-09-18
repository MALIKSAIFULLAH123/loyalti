<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Helper\Pagination;

class MemberMentionListener
{
    /**
     * @var MemberRepositoryInterface
     */
    protected $repository;

    public function __construct(MemberRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(?User $context, string $ownerType, string $ownerId, array $attributes): ?Paginator
    {
        if (!$context) {
            return null;
        }
        if ($ownerType != Group::ENTITY_TYPE) {
            return null;
        }

        $convertedAttributes = [
            'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
            'q'     => '',
        ];

        if (Arr::has($attributes, 'q') && $attributes['q'] != '') {
            Arr::set($convertedAttributes, 'q', Arr::get($attributes, 'q'));
        }

        if (Arr::has($attributes, 'limit')) {
            Arr::set($convertedAttributes, 'limit', Arr::get($attributes, 'limit'));
        }

        return $this->repository->getMembersForMention($context, $ownerId, $convertedAttributes);
    }
}
