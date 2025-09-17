<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Models\Phrase;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Models\UserGender as Model;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

/**
 * Class UserGenderRepository.
 *
 * @method Model getModel()
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserGenderRepository extends AbstractRepository implements UserGenderRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @param  string     $phrase
     * @return Model|null
     */
    public function findGenderByPhrase(string $phrase): ?Model
    {
        return $this->getModel()->newModelQuery()->where('phrase', $phrase)->first();
    }

    /**
     * @inheritDoc
     */
    public function viewGenders(User $context, array $attributes): Paginator
    {
        return $this->getModel()->newModelQuery()->simplePaginate();
    }

    /**
     * @inheritDoc
     */
    public function createGender(User $context, array $attributes): Model
    {
        $gender = new Model();
        $gender->fill($attributes);
        $gender->save();

        return $gender;
    }

    /**
     * @inheritDoc
     */
    public function updateGender(User $context, int $id, array $attributes): Model
    {
        /** @var Model $gender */
        $gender    = $this->find($id);
        $gender->fill($attributes);
        $gender->save();

        return $gender;
    }

    /**
     * @inheritDoc
     */
    public function deleteGender(User $context, int $id): bool
    {
        $gender = $this->find($id);

        if (!$gender instanceof Model || !$gender->is_custom) {
            abort(401, __p('phrase.permission_deny'));
        }

        $this->getPhraseRepository()->deleteWhere(['key' => $gender->phrase]);

        return (bool) $gender->delete();
    }

    /**
     * @inheritDoc
     */
    public function getForForms(User $context, ?array $where = null): array
    {
        $query = $this->getModel()->newModelQuery();

        if (!empty($where)) {
            $query->where($where);
        }

        return $query->get()
            ->collect()
            ->map(function (Model $gender, $key) {
                return [
                    'label' => $gender->name,
                    'value' => $gender->entityId(),
                ];
            })
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getSuggestion(array $params): array
    {
        $search   = Arr::get($params, 'q', null);
        $isCustom = Arr::get($params, 'is_custom', null);
        $query    = $this->getModel()->newModelQuery();

        $searchScope = new SearchScope($search);
        $searchScope->setFields(['name'])->setSearchText($search);
        $query = $query->addScope($searchScope);

        if (null !== $isCustom) {
            $query->where('is_custom', $isCustom);
        }

        return $query->orderBy('id')
            ->limit($params['limit'])
            ->get()
            ->collect()
            ->map(function (Model $gender, int $key) {
                return [
                    'label' => $gender->name,
                    'value' => $gender->entityId(),
                ];
            })
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function viewGendersForAdmin(User $context, array $attributes): LengthAwarePaginator
    {
        $query = $this->buildQueryViewGendersAdmin($attributes);

        return $query->paginate($attributes['limit']);
    }

    /**
     * @param  array<string, mixed> $attributes
     * @return Builder
     */
    private function buildQueryViewGendersAdmin(array $attributes): Builder
    {
        $search        = Arr::get($attributes, 'q');
        $defaultLocale = Language::getDefaultLocaleId();

        $query = $this->getModel()->newModelQuery()
            ->select(['user_gender.*']);

        if ($search) {
            $searchScope = new SearchScope(
                $search,
                ['user_gender.phrase', 'ps.text']
            );
            $searchScope->setTableField('phrase');
            $searchScope->setJoinedTable('phrases');
            $searchScope->setAliasJoinedTable('ps');
            $searchScope->setJoinedField('key');
            $query->where('ps.locale', '=', $defaultLocale);
            $query = $query->addScope($searchScope);
        }

        return $query;
    }

    public function getPhraseRepository(): PhraseRepositoryInterface
    {
        return resolve(PhraseRepositoryInterface::class);
    }

    public function getGenderOptions(): array
    {
        return Model::query()
            ->get()
            ->map(function ($gender) {
                return ['value' => $gender->entityId(), 'label' => $gender->name];
            })
            ->toArray();
    }

    public function viewAllGenders(array $ids = []): Collection
    {
        $query = Model::query();

        if (count($ids)) {
            $query->whereIn('id', $ids);
        }

        return $query->get()
            ->map(function ($gender) {
                $gender->phrase = __p($gender->phrase);

                return $gender;
            });
    }
}
