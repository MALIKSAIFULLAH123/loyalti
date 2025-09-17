<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Policies\StoryPolicy;

class LiveStreamBuildIntegrateFieldListener
{
    public function handle(User $context, array $showWhen, bool $isMobile = false): ?array
    {
        if (false === policy_check(StoryPolicy::class, 'create', $context)) {
            return null;
        }
        if ($isMobile) {
            return [
                MobileBuilder::switch('to_story')
                    ->label(__p('story::phrase.share_to_story'))
                    ->description(__p('story::phrase.share_to_story_description'))
                    ->showWhen($showWhen),
                MobileBuilder::selectSubForm('to_story')
                    ->label('SelectSubForm')
                    ->placeholder(__p('story::phrase.share_to_story'))
                    ->fullWidth(false)
                    ->setAttribute('icon', 'story')
                    ->variant('livestream')
                    ->options([
                        ['label' => __p('story::phrase.story_on'), 'value' => 1],
                        ['label' => __p('story::phrase.story_off'), 'value' => 0],
                    ]),
            ];
        }

        return [
            Builder::checkbox('to_story')
                      ->label(__p('story::phrase.share_to_story'))
                      ->description(__p('story::phrase.share_to_story_description'))
                      ->showWhen($showWhen),
        ];
    }
}
