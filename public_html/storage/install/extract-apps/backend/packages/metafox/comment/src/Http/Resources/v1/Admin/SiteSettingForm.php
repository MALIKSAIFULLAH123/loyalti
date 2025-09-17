<?php

namespace MetaFox\Comment\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Comment\Models\Comment as Model;
use MetaFox\Comment\Support\Helper;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 * @property Model $resource
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'comment';

        $vars = [
            'prefetch_comments_on_feed',
            'prefetch_replies_on_feed',
            'enable_photo',
            'enable_sticker',
            'enable_emoticon',
            'enable_giphy',
            'enable_thread',
            'show_reply',
            'enable_hash_check',
            'comments_to_check',
            'total_minutes_to_wait_for_comments',
            'sort_by',
            'sort_reply_by',
        ];

        $values = [];

        foreach ($vars as $var) {
            $var = $module . '.' . $var;

            $value = Settings::get($var);

            $value = $this->transformValue($var, $value);

            Arr::set($values, $var, $value);
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::switch('comment.enable_hash_check')
                ->label(__p('comment::admin.comment_hash_check'))
                ->description(__p('comment::admin.comment_hash_check_description'))
                ->setAttribute('checkedValue', true)
                ->setAttribute('uncheckedValue', false),
            Builder::text('comment.comments_to_check')
                ->label(__p('comment::admin.comment_to_check'))
                ->description(__p('comment::admin.comment_to_check_description'))
                ->required()
                ->showWhen(['truthy', 'comment.enable_hash_check'])
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->when(
                            Yup::when('enable_hash_check')
                                ->is(true)
                                ->then(
                                    Yup::number()
                                        ->required()
                                        ->min(1)
                                        ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                ),
            Builder::text('comment.total_minutes_to_wait_for_comments')
                ->label(__p('comment::admin.comment_minutes_to_wait_until_next_check'))
                ->description(__p('comment::admin.comment_minutes_to_wait_until_next_check_description'))
                ->showWhen(['truthy', 'comment.enable_hash_check'])
                ->required()
                ->yup(
                    Yup::number()
                        ->nullable()
                        ->when(
                            Yup::when('enable_hash_check')
                                ->is(true)
                                ->then(
                                    Yup::number()
                                        ->required()
                                        ->min(1)
                                        ->setError('typeError', __p('validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                ),
            Builder::switch('comment.enable_photo')
                ->label(__p('comment::admin.enable_photo_on_comment'))
                ->description(__p('comment::admin.enable_photo_on_comment_description')),
            Builder::switch('comment.enable_sticker')
                ->label(__p('comment::admin.enable_sticker_on_comment'))
                ->description(__p('comment::admin.enable_sticker_on_comment_description')),
            Builder::switch('comment.enable_emoticon')
                ->label(__p('comment::admin.enable_emojis_on_comment'))
                ->description(__p('comment::admin.enable_emojis_on_comment_description')),
            Builder::switch('comment.enable_giphy')
                ->label(__p('comment::admin.enable_giphy_on_comment'))
                ->description(__p('comment::admin.enable_giphy_on_comment_description')),
            Builder::switch('comment.enable_thread')
                ->label(__p('comment::admin.thread_display'))
                ->description(__p('comment::admin.thread_display_description')),
            Builder::switch('comment.show_reply')
                ->label(__p('comment::admin.show_replies_on_comment'))
                ->description(__p('comment::admin.show_replies_on_comment_description'))
                ->showWhen(['truthy', 'comment.enable_thread']),
            Builder::text('comment.prefetch_replies_on_feed')
                ->required()
                ->asNumber()
                ->label(__p('comment::admin.number_of_replies_will_be_shown_on_each_comment_on_item'))
                ->description(__p('comment::admin.number_of_replies_will_be_shown_on_each_comment_on_item_desc'))
                ->showWhen(['and', ['truthy', 'comment.show_reply'], ['truthy', 'comment.enable_thread']])
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                ),
            Builder::text('comment.prefetch_comments_on_feed')
                ->required()
                ->asNumber()
                ->label(__p('comment::admin.number_of_comment_will_be_shown_on_item'))
                ->description(__p('comment::admin.number_of_comment_will_be_shown_on_item_desc'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->min(0)
                ),
            Builder::choice('comment.sort_by')
                ->label(__p('comment::admin.order_of_comments_in_feed'))
                ->disableClearable()
                ->options(Helper::getSortOptions())
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::choice('comment.sort_reply_by')
                ->label(__p('comment::admin.order_of_replies'))
                ->disableClearable()
                ->options(Helper::getReplySortOptions())
                ->yup(
                    Yup::string()
                        ->required()
                ),
        );

        $this->addDefaultFooter(true);
    }

    protected function transformValue(string $var, mixed $value): mixed
    {
        switch ($var) {
            case 'comment.comments_to_check':
            case 'comment.total_minutes_to_wait_for_comments':
                if (0 === $value) {
                    $value = null;
                }

                break;
        }

        return $value;
    }
}
