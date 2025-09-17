<?php

namespace MetaFox\Layout\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Layout\Models\Variant;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Variant.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface VariantRepositoryInterface
{
    /**
     * Get activated theme variants.
     * @return Collection
     */
    public function getActiveVariants(): Collection;

    /**
     * @return array<string>
     */
    public function getActiveVariantIds(): array;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Variant
     * @throws AuthorizationException
     */
    public function updateVariant(User $context, int $id, array $attributes): Variant;
}
