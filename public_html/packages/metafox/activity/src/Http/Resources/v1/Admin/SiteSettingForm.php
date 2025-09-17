<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Activity\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Activity\Support\Support;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * Class SiteSettingForm.
 *
 * @driverName feed
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'activity';

        $vars = [
            'feed.allow_choose_sort_on_feeds',
            'feed.sort_default',
            'feed.enable_check_in',
            'feed.enable_tag_friends',
            'feed.enable_hide_feed',
            'feed.limit_days',
            'feed.refresh_time',
            'feed.top_stories_update',
            'feed.total_likes_to_display',
            'feed.spam_check_status_updates',
            'feed.check_new_in_minutes',
            'feed.total_pin_in_homepage',
            'feed.total_pin_in_profile',
            'feed.add_comment_as_feed',
            'feed.sponsored_feed_cache_time',
            'feed.only_friends',
            'feed.maximum_characters_for_post_status',
        ];

        $value = [];

        foreach ($vars as $var) {
            $var = $module . '.' . $var;
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic([]);

        $basic->addFields(
            Builder::switch('activity.feed.only_friends')
                ->label(__p('activity::admin.friends_only_label'))
                ->description(__p('activity::admin.friends_only_description')),
            Builder::text('activity.feed.total_pin_in_homepage')
                ->label(__p('activity::admin.pin_homepage_label'))
                ->description(__p('activity::admin.pin_homepage_description', ['number' => 0]))
                ->yup(
                    Yup::number()
                        ->int()
                        ->min(0)
                        ->setError('typeError', __p('core::phrase.this_field_must_be_number'))
                ),
            Builder::text('activity.feed.total_pin_in_profile')
                ->label(__p('activity::admin.pin_profile_label'))
                ->description(__p('activity::admin.pin_profile_description', ['number' => 0]))
                ->yup(
                    Yup::number()
                        ->int()
                        ->min(0)
                        ->setError('typeError', __p('core::phrase.this_field_must_be_number'))
                ),
            /*Builder::text('activity.feed.limit_days')
                ->label(__p('activity::admin.feed_limit_days_label'))
                ->description(__p('activity::admin.feed_limit_days_description'))
                ->yup(
                    Yup::number()->int()->min(0)
                ),*/
            Builder::switch('activity.feed.enable_tag_friends')
                ->label(__p('activity::admin.enable_tag_friends_label'))
                ->description(__p('activity::admin.enable_tag_friends_description')),
            Builder::switch('activity.feed.enable_check_in')
                ->label(__p('activity::admin.enable_checkin_label'))
                ->description(__p('activity::admin.enable_checkin_description')),
            Builder::switch('activity.feed.enable_hide_feed')
                ->label(__p('activity::admin.enable_hide_feed_label'))
                ->description(__p('activity::admin.enable_hide_feed_description')),
            Builder::choice('activity.feed.sort_default')
                ->label(__p('activity::admin.sort_default_label'))
                ->description(__p('activity::admin.sort_default_description'))
                ->options(Support::getSortOptions()),
            /*Builder::switch('activity.feed.allow_choose_sort_on_feeds')
                ->label(__p('activity::admin.allow_choosing_sort_on_feed_label'))
                ->description(__p('activity::admin.allow_choosing_sort_on_feed_description')),
            Builder::choice('activity.feed.top_stories_update')
                ->label(__p('activity::admin.top_stories_update_label'))
                ->description(__p('activity::admin.top_stories_update_description'))
                ->options(Support::getTopStoriesUpdateOptions()),*/
            Builder::text('activity.feed.spam_check_status_updates')
                ->label(__p('activity::admin.spam_check_status_updates_label'))
                ->asNumber()
                ->description(__p('activity::admin.spam_check_status_updates_description'))
                ->yup(
                    Yup::number()
                        ->min(0)
                ),
            Builder::text('activity.feed.check_new_in_minutes')
                ->label(__p('activity::admin.check_new_in_minutes_label'))
                ->description(__p('activity::admin.check_new_in_minutes_description'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                        ->setError('min', __p('activity::validation.check_new_min', ['number' => 0]))
                        ->setError('typeError', __p('activity::validation.check_new_min', ['number' => 0]))
                        ->setError('required', __p('activity::validation.check_new_min', ['number' => 0]))
                ),
            /*Builder::switch('activity.feed.add_comment_as_feed')
                ->label(__p('activity::admin.add_comment_as_feed_label'))
                ->description(__p('activity::admin.add_comment_as_feed_description')),
            Builder::text('activity.feed.sponsored_feed_cache_time')
                ->label(__p('activity::admin.sponsored_feed_cache_time_label'))
                ->description(__p('activity::admin.sponsored_feed_cache_time_description'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                        ->int(__p('activity::validation.check_new_min', ['number' => 0]))
                        ->setError('min', __p('activity::validation.check_new_min', ['number' => 0]))
                        ->setError('typeError', __p('activity::validation.check_new_min', ['number' => 0]))
                        ->setError('required', __p('activity::validation.check_new_min', ['number' => 0]))
                ),*/
            Builder::text('activity.feed.maximum_characters_for_post_status')
                ->label(__p('activity::admin.maximum_characters_for_post_status_label'))
                ->description(__p('activity::admin.maximum_characters_for_post_status_description'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                        ->setError('min', __p('activity::validation.check_new_min', ['number' => 0]))
                        ->setError('typeError', __p('activity::validation.check_new_min', ['number' => 0]))
                        ->setError('required', __p('activity::validation.check_new_min', ['number' => 0]))
                ),
        );

        $this->addDefaultFooter(true);
    }
}
