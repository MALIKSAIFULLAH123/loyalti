<?php

namespace MetaFox\Marketplace\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Marketplace\Jobs\DeleteListingJob;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Marketplace\Repositories\ListingAdminRepositoryInterface;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\SortScope;
use MetaFox\Marketplace\Support\Browse\Scopes\Listing\ViewAdminScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class ListingAdminRepository.
 * @property Listing $model
 * @method   Listing getModel()
 * @method   Listing find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 */
class ListingAdminRepository extends AbstractRepository implements ListingAdminRepositoryInterface
{
    use HasSponsor;
    use HasSponsorInFeed;
    use HasApprove;
    use CollectTotalItemStatTrait;

    public function model(): string
    {
        return Listing::class;
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewMarketplaceListings(User $context, array $attributes): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $query = $this->buildQueryViewListings($context, $attributes);

        $relation = ['marketplaceText', 'photos', 'tagData'];

        return $query->with($relation);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildQueryViewListings(User $context, array $attributes): Builder
    {
        $sort        = Arr::get($attributes, 'sort');
        $sortType    = Arr::get($attributes, 'sort_type');
        $when        = Arr::get($attributes, 'when');
        $view        = Arr::get($attributes, 'view');
        $search      = Arr::get($attributes, 'q');
        $categoryId  = Arr::get($attributes, 'category_id');
        $countryIso  = Arr::get($attributes, 'country_iso');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();

        $sortScope = new SortScope();
        $sortScope->setSort($sort)
            ->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewAdminScope();
        $viewScope->setUserContext($context)
            ->setView($view);

        $query = $this->getModel()->newQuery();

        if (MetaFoxConstant::EMPTY_STRING !== $search) {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($categoryId > 0) {
            $categoryScope = new CategoryScope();

            $categoryScope->setCategories($this->categoryRepository()->getChildrenIds($categoryId));

            $query = $query->addScope($categoryScope);
        }

        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);

        if ($searchOwner) {
            $searchScope->setAliasJoinedTable('owner');
            $searchScope->setSearchText($searchOwner);
            $searchScope->setFieldJoined('owner_id');
            $query->addScope($searchScope);
        }

        if ($searchUser) {
            $searchScope->setAliasJoinedTable('user');
            $searchScope->setSearchText($searchUser);
            $searchScope->setFieldJoined('user_id');
            $query->addScope($searchScope);
        }

        if ($createdFrom) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        if (null !== $countryIso) {
            $query->where('marketplace_listings.country_iso', '=', $countryIso);
        }

        return $query
            ->addScope($whenScope)
            ->addScope($viewScope)
            ->addScope($sortScope);
    }

    /**
     * @inheritDoc
     */
    public function deleteMarketplaceListing(User $context, int $id): bool
    {
        if (!$this->delete($id)) {
            return false;
        }

        DeleteListingJob::dispatch($id);

        return true;
    }
}
