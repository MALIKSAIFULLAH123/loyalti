<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Activity\Support\Support;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserPrivacy;

class SharedDataPreparationListener
{
    public function handle(string $postType, array $data): ?array
    {
        if (Support::SHARED_TYPE !== $postType) {
            return null;
        }

        $context = user();

        Arr::set($data, 'owners', [$context->entityId()]);

        $message = __p('activity::phrase.shared_successfully');

        if (Arr::has($data, 'user_status')) {
            $message = __p('activity::phrase.shared_to_feed');
        }

        Arr::set($data, 'success_message', $message);

        if (!Arr::has($data, 'privacy')) {
            $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'feed.item_privacy');
            Arr::set($data, 'privacy', false !== $privacy ? $privacy : MetaFoxPrivacy::EVERYONE);
        }

        return $data;
    }
}
