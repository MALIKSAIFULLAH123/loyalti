<?php

namespace MetaFox\Localize\Repositories;

use Illuminate\Support\Collection;
use MetaFox\Localize\Models\Language;
use MetaFox\User\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Language.
 * @mixin BaseRepository
 */
interface LanguageRepositoryInterface
{
    /**
     * @param bool|null $active
     *
     * @return array<string,string>
     */
    public function getOptions(bool $active = null): array;

    public function getActiveLanguages(): Collection;

    /**
     * @return Language|null
     */
    public function getDefaultLanguage(): ?Language;

    public function updateActive(User $context, int $id, bool $isActive): bool;

    /**
     * @param User $context
     * @param int  $id
     */
    public function deleteLanguage(User $context, int $id): bool;

    /**
     * @param  array      $codes
     * @return Collection
     */
    public function viewAllLanguages(array $codes = []): Collection;

    /**
     * @param int $id
     */
    public function addMissPhrases(int $id): Language;
}
