<?php

namespace MetaFox\LiveStreaming\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Repositories\LiveVideoAdminRepositoryInterface;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\DurationScope;
use MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo\ViewAdminScope;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class LiveVideoAdminRepository.
 */
class LiveVideoAdminRepository extends AbstractRepository implements LiveVideoAdminRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use RepoTrait;

    public const DEFAULT_THUMBNAIL_PLAYBACK = 'https://image.mux.com/';

    public function model(): string
    {
        return Model::class;
    }

    public function viewLiveVideos(ContractUser $context, array $attributes): Builder
    {
        $sort        = Arr::get($attributes, 'sort');
        $sortType    = Arr::get($attributes, 'sort_type');
        $view        = Arr::get($attributes, 'view');
        $search      = Arr::get($attributes, 'q');
        $duration    = Arr::get($attributes, 'duration');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();
        $this->withUserMorphTypeActiveScope();

        // Scopes.
        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);

        $sortScope = new SortScope($sort, $sortType);
        $viewScope = new ViewAdminScope();
        $viewScope->setUserContext($context)->setView($view);

        $query = $this->getModel()->newQuery();

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

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($duration) {
            $query->addScope(new DurationScope($duration));
        }

        $relations = ['liveVideoText', 'user', 'userEntity'];

        return $query
            ->with($relations)
            ->addScope($sortScope)
            ->addScope($viewScope);
    }

    public function getThumbnailPlayback(int $id): ?string
    {
        $liveVideo = $this->withUserMorphTypeActiveScope()->find($id);
        if (!$liveVideo) {
            return null;
        }

        if ($liveVideo->images) {
            return $liveVideo->image;
        }

        if (!$liveVideo->playback) {
            return null;
        }

        $customThumbnailUrl  = Settings::get('livestreaming.custom_thumbnail_playback_url');
        $defaultThumbnailUrl = self::DEFAULT_THUMBNAIL_PLAYBACK;
        $thumbnailPlayback   = !empty($customThumbnailUrl) ? trim($customThumbnailUrl, '/') . '/' : $defaultThumbnailUrl;

        return $thumbnailPlayback . $liveVideo->playback->playback_id . '/thumbnail.png';
    }
}
