<?php

namespace MetaFox\Layout\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Layout\Repositories\VariantRepositoryInterface;
use MetaFox\Layout\Models\Variant;
use MetaFox\Layout\Repositories\ThemeRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * class VariantRepository.
 */
class VariantRepository extends AbstractRepository implements VariantRepositoryInterface
{
    public function model()
    {
        return Variant::class;
    }

    public function getActiveVariants(): Collection
    {
        $activeThemeIds = $this->themeRepository()->getActiveThemeIds();

        return Variant::query()
            ->whereIn('theme_id', $activeThemeIds)
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();
    }

    public function getActiveVariantIds(): array
    {
        return $this->getActiveVariants()
            ->map(fn ($row) => sprintf('%s:%s', $row->theme_id, $row->variant_id))
            ->toArray();
    }

    public function updateVariant(User $context, int $id, array $attributes): Variant
    {
        $variant    = $this->find($id);
        $tempFileId = Arr::get($attributes, 'thumbnail.temp_file');

        if (is_numeric($tempFileId)) {
            $storageFile = upload()->getFile($tempFileId);

            Arr::set($attributes, 'thumb_id', $storageFile->entityId());

            upload()->rollUp($tempFileId);

            if ($variant->thumb_id) {
                upload()->rollUp($variant->thumb_id);
            }
        }

        $variant->fill($attributes);

        $variant->save();
        $variant->refresh();

        return $variant;
    }

    private function themeRepository(): ThemeRepositoryInterface
    {
        return resolve(ThemeRepositoryInterface::class);
    }
}
