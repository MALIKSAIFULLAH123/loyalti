<?php

namespace MetaFox\Announcement\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Style;
use MetaFox\Announcement\Policies\AnnouncementPolicy;
use MetaFox\Announcement\Repositories\AnnouncementCloseRepositoryInterface;
use MetaFox\Announcement\Repositories\AnnouncementContentRepositoryInterface;
use MetaFox\Announcement\Repositories\AnnouncementRepositoryInterface;
use MetaFox\Announcement\Repositories\HiddenRepositoryInterface;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class AnnouncementRepository.
 * @property Announcement $model
 * @method   Announcement getModel()
 * @method   Announcement find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AnnouncementRepository extends AbstractRepository implements AnnouncementRepositoryInterface
{
    public function model(): string
    {
        return Announcement::class;
    }

    public function getHiddenRepository(): HiddenRepositoryInterface
    {
        return resolve(HiddenRepositoryInterface::class);
    }

    public function getCloseRepository(): AnnouncementCloseRepositoryInterface
    {
        return resolve(AnnouncementCloseRepositoryInterface::class);
    }

    public function viewAnnouncementsWithLastId(User $context, array $attributes): array
    {
        $limit = Arr::get($attributes, 'limit', 10);

        $lastId = Arr::get($attributes, 'last_id');

        $query = $this->buildQuery($context);

        $query->where(function (Builder $whereQuery) {
            $whereQuery
                ->whereNull('announcement_views.id')
                ->orWhere('announcements.can_be_closed', '=', 0);
        });

        $total = $query->count();

        if (is_numeric($lastId) && $lastId > 0) {
            /**
             * Because the buildQuery method is setting the order with desc
             */
            $query->where('announcements.id', '<', $lastId);
        }

        $items = $query
            ->with(['announcementText', 'style', 'views'])
            ->limit($limit)
            ->get(['announcements.*']);

        return [$total, $items];
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Collection
     */
    public function viewAnnouncements(User $context, array $attributes): Paginator
    {
        $limit = Arr::get($attributes, 'limit', 10);

        $query = $this->buildQuery($context);

        $query->where(function (Builder $whereQuery) {
            $whereQuery
                ->whereNull('announcement_views.id')
                ->orWhere('announcements.can_be_closed', '=', 0);
        });

        return $query
            ->with(['announcementText', 'style', 'views'])
            ->paginate($limit);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Announcement
     * @throws AuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewAnnouncement(User $context, int $id): Announcement
    {
        $resource = $this->with(['announcementText', 'style', 'userEntity'])->find($id);

        policy_authorize(AnnouncementPolicy::class, 'view', $context, $resource);

        return $resource;
    }

    public function getTotalUnread(User $context): int
    {
        $query = $this->buildQuery($context);

        $query->whereNull('announcement_views.id');

        return $query->count();
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Announcement
     * @throws AuthorizationException
     * @throws Exception
     */
    public function createAnnouncement(User $context, array $attributes): Announcement
    {
        policy_authorize(AnnouncementPolicy::class, 'create', $context);

        $attributes = $this->cleanData($attributes);

        $attributes = array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]);

        /** @var Announcement $announcement */
        $announcement = $this->getModel()->newModelInstance();
        $announcement->fill($attributes);
        $announcement->save();
        $announcement->refresh();

        $this->getContentRepository()->updateOrCreateContent($announcement, $attributes);

        $announcement->loadMissing(['contents', 'masterContent', 'content', 'roles', 'style']);

        return $announcement;
    }

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Announcement
     * @throws AuthorizationException | Exception
     */
    public function updateAnnouncement(User $context, int $id, array $attributes): Announcement
    {
        $announcement = $this->find($id);
        policy_authorize(AnnouncementPolicy::class, 'update', $context, $announcement);

        $attributes = $this->cleanData($attributes);

        $announcement->fill($attributes);

        //assign style to announcement
        if (isset($attributes['style'])) {
            $styleId    = $announcement->style?->entityId() ?? 0;
            $isNewStyle = $attributes['style'] != $styleId;
            if ($isNewStyle) {
                $style = Style::query()->findOrFail($attributes['style']);
                $announcement->style()->associate($style);
            }
        }

        $announcement->save();
        $announcement->refresh();

        $this->getContentRepository()->updateOrCreateContent($announcement, $attributes);

        return $announcement;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return int
     * @throws AuthorizationException
     */
    public function deleteAnnouncement(User $context, int $id): int
    {
        $announcement = $this->find($id);

        policy_authorize(AnnouncementPolicy::class, 'delete', $context, $announcement);

        return $this->delete($id);
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Announcement
     */
    public function hideAnnouncement(User $context, int $id): Announcement
    {
        $announcement = $this->find($id);

        $this->getHiddenRepository()->createHidden($context, $announcement);

        return $announcement;
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    protected function cleanData(array $attributes): array
    {
        $parser = parse_input();

        return collect($attributes)->map(function ($value, $key) use ($parser) {
            if (in_array($key, ['subject_var', 'intro_var'])) {
                foreach ($value as $var => $phraseVal) {
                    $value[$var] = $parser->clean($phraseVal);
                }
            }

            return $value;
        })->toArray();
    }

    /**
     * @inheritDoc
     */
    public function viewAnnouncementsForAdmin(User $context, array $attributes): Paginator
    {
        $limit       = $attributes['limit'];
        $search      = Arr::get($attributes, 'q');
        $roleId      = Arr::get($attributes, 'role_id');
        $style       = Arr::get($attributes, 'style');
        $startFrom   = Arr::get($attributes, 'start_from');
        $startTo     = Arr::get($attributes, 'start_to');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');

        $query         = $this->getModel()->newModelQuery()->select(['announcements.*']);
        $defaultLocale = Language::getDefaultLocaleId();

        if ($search) {
            $searchScope = new SearchScope($search, ['announcements.subject_var', 'ps.text']);
            $searchScope->setTableField('subject_var');
            $searchScope->setJoinedTable('phrases');
            $searchScope->setAliasJoinedTable('ps');
            $searchScope->setJoinedField('key');

            $query->where('ps.locale', '=', $defaultLocale);
            $query = $query->addScope($searchScope);
        }

        if (null !== $style) {
            $query->where('announcements.style_id', '=', $style);
        }

        if ($roleId) {
            $query->where(function ($innerQuery) use ($roleId) {
                $innerQuery->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('role_id', '=', $roleId);
                });

                $innerQuery->orWhereDoesntHave('roles');
            });
        }

        if ($startFrom) {
            $query->where('announcements.start_date', '>=', $startFrom);
        }

        if ($startTo) {
            $query->where('announcements.start_date', '<=', $startTo);
        }

        if ($createdFrom) {
            $query->where('announcements.created_at', '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where('announcements.created_at', '<=', $createdTo);
        }

        return $query
            ->with(['announcementText', 'style', 'roles'])
            ->limit($limit)
            ->orderBy('announcements.id', 'desc')
            ->paginate();
    }

    protected function getPhraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function activateAnnouncement(User $context, int $id): Announcement
    {
        $announcement = $this->find($id);

        policy_check(AnnouncementPolicy::class, 'update', $context, $announcement);

        $announcement->update(['is_active' => 1]);

        return $announcement->refresh();
    }

    /**
     * @inheritDoc
     */
    public function deactivateAnnouncement(User $context, int $id): Announcement
    {
        $announcement = $this->find($id);

        policy_check(AnnouncementPolicy::class, 'update', $context, $announcement);

        $announcement->update(['is_active' => 0]);

        return $announcement->refresh();
    }

    /**
     * @param User $context
     *
     * @return Builder
     */
    private function buildQuery(User $context): Builder
    {
        $query = $this->getModel()
            ->newModelQuery()
            ->select(['announcements.*'])
            ->from('announcements')
            ->leftJoin('announcement_views', function (JoinClause $join) use ($context) {
                $join->on('announcements.id', '=', 'announcement_views.announcement_id')
                    ->where('announcement_views.user_id', '=', $context->entityId());
            })
            ->where('announcements.is_active', '=', 1)
            ->where('announcements.start_date', '<=', Carbon::now());

        if (!$context->hasSuperAdminRole()) {
            $query = $this->applyRoleQuery($query, $context);
            $query = $this->applyLocationsQuery($query, $context);
            $query = $this->applyGendersQuery($query, $context);
        }

        $idCloses = $this->getCloseRepository()
            ->getCloseAnnouncements($context)
            ->pluck('announcement_id')
            ->toArray();

        return $query->whereNotIn('announcements.id', $idCloses)
            ->orderBy('announcements.id', 'desc');
    }

    private function applyRoleQuery(Builder $query, User $user): Builder
    {
        $contextRole = resolve(RoleRepositoryInterface::class)->roleOf($user);
        if (!$contextRole instanceof Role) {
            return $query;
        }

        return $query->where(function (Builder $whereQuery) use ($contextRole) {
            $whereQuery->doesntHave('roles')
                ->orWhereHas('roles', function (Builder $hasQuery) use ($contextRole) {
                    $hasQuery->where('role_id', '=', $contextRole->entityId());
                });
        });
    }

    private function applyGendersQuery(Builder $query, User $context): Builder
    {
        if (!$context instanceof HasUserProfile || !$context->profile->gender_id) {
            return $query->doesntHave('genders');
        }

        return $query->where(function (Builder $where) use ($context) {
            $where->doesntHave('genders')
                ->orWhereHas('genders', function (Builder $hasQuery) use ($context) {
                    $hasQuery->where('announcement_gender_data.gender_id', '=', $context->profile->gender_id);
                });
        });
    }

    private function applyLocationsQuery(Builder $query, User $context): Builder
    {
        if (!$context instanceof HasUserProfile || !$context->profile->country_iso) {
            return $query->doesntHave('countries');
        }

        return $query->where(function (Builder $where) use ($context) {
            $where->doesntHave('countries')
                ->orWhereHas('countries', function (Builder $hasQuery) use ($context) {
                    $hasQuery->where('announcement_country_data.country_iso', '=', $context->profile->country_iso);
                });
        });
    }

    /**
     * @inheritDoc
     *
     * @param User $context
     * @param int  $id
     *
     * @return Announcement
     */
    public function closeAnnouncement(User $context, int $id): Announcement
    {
        $announcement = $this->find($id);

        $this->getCloseRepository()->closeAnnouncement($context, $announcement);

        return $announcement;
    }

    protected function getContentRepository(): AnnouncementContentRepositoryInterface
    {
        return resolve(AnnouncementContentRepositoryInterface::class);
    }
}
