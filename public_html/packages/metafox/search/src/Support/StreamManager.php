<?php

namespace MetaFox\Search\Support;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Core\Repositories\Contracts\PrivacyMemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Search\Models\Search;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

/**
 * Class StreamManager.
 */
class StreamManager
{
    /**
     * @var array
     */
    protected array $meta = [];

    /** @var User|null */
    protected $user;

    /**
     * @var int
     */
    protected int $limit = Pagination::DEFAULT_ITEM_PER_PAGE;

    /**
     * @var string
     */
    protected string $searchText = '';

    /**
     * @var string|null
     */
    protected ?string $view = null;

    /**
     * @var int
     */
    protected int $continuousTry = 1;

    /**
     * @var PrivacyMemberRepositoryInterface
     */
    protected $privacyRepository;

    /**
     * @var SearchRepositoryInterface
     */
    protected $searchRepository;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param  array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return self
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSearchText(): ?string
    {
        return $this->searchText;
    }

    /**
     * @return self
     */
    public function setSearchText(string $searchText)
    {
        $this->searchText = $searchText;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getView(): ?string
    {
        return $this->view;
    }

    /**
     * @return self
     */
    public function setView(string $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param SearchRepositoryInterface        $searchRepository
     * @param PrivacyMemberRepositoryInterface $privacyRepository
     */
    public function __construct(
        SearchRepositoryInterface $searchRepository,
        PrivacyMemberRepositoryInterface $privacyRepository
    ) {
        $this->searchRepository = $searchRepository;

        $this->privacyRepository = $privacyRepository;
    }

    /**
     * @param int|null    $lastSearchId
     * @param string|null $timeFrom
     * @param string|null $timeTo
     *
     * @return Builder
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function buildQuery(): Builder
    {
        return resolve(SearchRepositoryInterface::class)->buildQuery($this->getUser(), $this->getAttributes());
    }

    /**
     * @return Collection
     * @throws Exception
     */
    protected function fetchStream(?int $limit = null): Collection
    {
        $query = $this->buildQuery();

        if (null !== $limit) {
            $query->limit($limit);
        }

        return $query
            ->select('search_items.*')
            ->orderBy('search_items.id', 'DESC')
            ->groupBy(['search_items.item_id', 'search_items.item_type', 'search_items.id'])
            ->get();
    }

    /**
     * @param mixed    $collection
     * @param int|null $need
     * @param int      $try
     *
     * @return array
     * @throws Exception
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchStreamContinuous(?Collection $collection = null, ?int $need = null, int $try = 0): array
    {
        $need = $need ?: $this->getLimit();

        if (null === $collection) {
            $collection = $this->fetchStream($need)->unique(function ($item) {
                return "{$item->item_id}_{$item->item_type}";
            });
        } else {
            $lastSearch = $collection->last();

            $lastSearchId = $lastSearch instanceof Search ? $lastSearch->entityId() : null;

            Arr::set($this->attributes, 'last_search_id', $lastSearchId);

            // Search by last stream id.
            $newData = $this->fetchStream()->unique(function ($item) {
                return "{$item->item_id}_{$item->item_type}";
            });

            if ($newData->count() == 0) {
                return [$collection, $this->meta];
            }

            $hasNewItemInsert = false;

            foreach ($newData as $item) {
                if (!$item->owner?->isApproved()) {
                    continue;
                }

                $itemId = $item->item_id;

                $itemType = $item->item_type;

                $contains = $collection->contains(function ($value) use ($itemId, $itemType) {
                    return $value->item_id == $itemId && $value->item_type == $itemType;
                });

                if (!$contains) {
                    $collection->add($item);

                    $hasNewItemInsert = true;
                }
            }

            if (!$hasNewItemInsert) {
                $try++;

                // If we try x times and get nothing, return current collection.
                if ($try >= $this->continuousTry) {
                    return [$collection, $this->meta];
                }
            }
        }

        if ($collection->count() == 0) {
            return [$collection, $this->meta];
        }

        if ($collection->count() < $need) {
            /** @var int $left */
            $left = $need - $collection->count();

            [$collection] = $this->fetchStreamContinuous($collection, $left, $try);
        }

        $last = $collection->last();

        if ($last instanceof Search) {
            Arr::set($this->meta, 'last_search_id', $last->entityId());
        }

        return [$collection, $this->meta];
    }
}
