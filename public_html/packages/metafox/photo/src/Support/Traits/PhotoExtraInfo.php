<?php

namespace MetaFox\Photo\Support\Traits;

use Illuminate\Support\Arr;
use MetaFox\Photo\Support\Facades\Photo as PhotoFacade;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * Trait PhotoExtraInfo.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait PhotoExtraInfo
{
    public function addExtraPhotoInfoRule(array $rules, string $fieldName): array
    {
        $extraInfoRules = [
            "$fieldName.*.extra_info"       => ['sometimes', 'nullable', 'array'],
            "$fieldName.*.extra_info.title" => ['sometimes', 'nullable'],
            "$fieldName.*.extra_info.text"  => ['sometimes', 'nullable'],
        ];

        if ($this->hasEnableMatureImage()) {
            $extraInfoRules["$fieldName.*.extra_info.mature"] = ['sometimes', 'nullable', 'numeric', new AllowInRule(PhotoFacade::getAllowMatureContent())];
        }

        return array_merge($rules, $extraInfoRules);
    }

    private function transformExtraPhotoInfo(array $data, string $fieldName): array
    {
        $files = Arr::get($data, $fieldName);

        if (empty($files)) {
            return $data;
        }

        $allowFields = $this->getAllowExtraPhotoInfoFields();

        foreach ($files as &$file) {
            $extraInfo = Arr::get($file, 'extra_info');

            if (empty($extraInfo)) {
                continue;
            }

            $extraInfo = array_intersect_key($extraInfo, array_flip($allowFields));

            Arr::set($file, 'extra_info', $extraInfo);
        }

        Arr::set($data, $fieldName, $files);

        return $data;
    }

    public function getAllowExtraPhotoInfoFields(): array
    {
        $fields = ['title', 'text'];

        if ($this->hasEnableMatureImage()) {
            $fields[] = 'mature';
        }

        return $fields;
    }

    private function hasEnableMatureImage(): bool
    {
        $context = user();

        return $context->hasPermissionTo('photo.add_mature_image');
    }
}
