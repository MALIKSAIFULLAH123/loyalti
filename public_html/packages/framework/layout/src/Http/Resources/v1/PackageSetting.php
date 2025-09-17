<?php

namespace MetaFox\Layout\Http\Resources\v1;

use MetaFox\Layout\Models\Variant;
use MetaFox\Layout\Repositories\VariantRepositoryInterface;

class PackageSetting
{
    private function getVariants(): array
    {
        $default = config('app.mfox_site_theme');

        $result = [];
        /** @var Variant[] $rows */
        $rows = resolve(VariantRepositoryInterface::class)->getActiveVariants();
        foreach ($rows as $row) {
            $result[] = [
                'id'    => sprintf('%s:%s', $row->theme_id, $row->variant_id),
                'title' => $row->title,
                'image' => $row->imageUrl,
                '_id'   => $row->identity,
            ];
        }

        uasort($result, function ($a, $b) use ($default) {
            return $a['_id'] === $default ? -1 : 1;
        });

        return array_values($result);
    }

    public function getWebSettings(): array
    {
        return [
            'variants' => $this->getVariants(),
        ];
    }
}
