<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

class UpdatePhotoGroupListener
{
    /**
     * Photo set feed content won't apply to every single photo. DO NOT assign content to per photo.
     *
     * @param  User                 $context
     * @param  Content|null         $group
     * @param  array<string, mixed> $params
     * @return bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(User $context, ?Content $group = null, array $params = []): ?bool
    {
        if (!$group instanceof PhotoGroup) {
            return null;
        }

        $groupPrivacy = $params['privacy'] ?? $group->privacy;

        $groupPrivacyList = $params['list'] ?? $group->getPrivacyListAttribute();

        $groupParams = [
            'privacy' => $groupPrivacy,
        ];

        if (Arr::has($params, 'content')) {
            Arr::set($groupParams, 'content', Arr::get($params, 'content'));
        }

        $group->fill($groupParams);

        $group->setPrivacyListAttribute($groupPrivacyList);

        return $group->save();
    }
}
