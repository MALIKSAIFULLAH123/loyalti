<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet\Admin;

use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Sticker\Models\Sticker;
use MetaFox\Sticker\Models\StickerSet as Model;
use MetaFox\Sticker\Repositories\StickerSetAdminRepositoryInterface;
use MetaFox\Sticker\Repositories\StickerSetRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateStickerSetForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateStickerSetForm extends StoreStickerSetForm
{
    public function boot(StickerSetRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        resolve(StickerSetAdminRepositoryInterface::class)
            ->checkCanUpdate($this->resource);

        $values = [
            'title'     => $this->resource->title,
            'is_active' => $this->resource->is_active,
        ];
        $values = $this->prepareAttachedStickers($values);
        $this->title(__p('sticker::phrase.update_sticker_set'))
            ->action('admincp/sticker/sticker-set/' . $this->resource->id)
            ->asPut()
            ->setValue($values);
    }

    protected function prepareAttachedStickers(array $values): array
    {
        $items = [];

        $stickers = $this->resource->stickers()
            ->where('is_deleted', '!=', Sticker::IS_DELETED)
            ->orderBy('ordering')
            ->get();

        if ($stickers->count()) {
            $items = $stickers->map(function ($sticker) {
                $response = ResourceGate::asItem($sticker, null);

                return array_merge($response->toArray(request()), [
                    'status' => MetaFoxConstant::FILE_UPDATE_STATUS,
                ]);
            })
                ->values()
                ->toArray();
        }

        Arr::set($values, 'file', $items);

        return $values;
    }

    protected function isEdit(): bool
    {
        return true;
    }
}
