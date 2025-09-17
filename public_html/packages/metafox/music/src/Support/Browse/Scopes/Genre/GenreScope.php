<?php

namespace MetaFox\Music\Support\Browse\Scopes\Genre;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class GenreScope extends BaseScope
{
    /**
     * @param string $itemType
     * @return $this
     */
    public function setItemType(string $itemType): static
    {
        $this->itemType = $itemType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getItemType(): ?string
    {
        return $this->itemType;
    }

    /**
     * @param int $genreId
     * @return $this
     */
    public function setGenreId(int $genreId): static
    {
        $this->genreId = $genreId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGenreId(): ?int
    {
        return $this->genreId;
    }

    public function __construct(protected ?string $itemType, protected ?int $genreId)
    {
    }

    public function apply(Builder $builder, Model $model)
    {
        $itemType = $this->getItemType();
        $genreId = $this->getGenreId();

        if (!$itemType) {
            return;
        }

        if (!$genreId) {
            return;
        }

        $table = $model->getTable();
        $idField = $model->getKeyName();
        $genreIds = $this->genreRepository()->getChildrenIds($genreId);
        $builder->join('music_genre_data', function (JoinClause $joinClause) use ($table, $idField, $itemType, $genreIds) {
            $joinClause->on('music_genre_data.item_id', '=', sprintf('%s.%s', $table, $idField))
                ->where('music_genre_data.item_type', '=', $itemType)
                ->whereIn('music_genre_data.genre_id', $genreIds);
        })
            ->join('music_genres', function (JoinClause $joinClause) {
                $joinClause->on('music_genres.id', '=', 'music_genre_data.genre_id')
                    ->where('music_genres.is_active', '=', 1);
            });
    }

    private function genreRepository(): GenreRepositoryInterface
    {
        return resolve(GenreRepositoryInterface::class);
    }
}
