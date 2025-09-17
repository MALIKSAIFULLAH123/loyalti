<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Quiz\Listeners;

use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Notifications\NewQuizToFollowerNotification;
use MetaFox\Quiz\Notifications\QuizApproveNotifications;
use MetaFox\Quiz\Notifications\QuizResubmitNotifications;
use MetaFox\Quiz\Notifications\SubmitResultNotifications;
use MetaFox\Quiz\Policies\QuizPolicy;
use MetaFox\Quiz\Support\Handlers\EditPermissionListener;

class PackageSettingListener extends BasePackageSettingListener
{
    public function getActivityTypes(): array
    {
        return [
            [
                'type'            => Quiz::ENTITY_TYPE,
                'entity_type'     => Quiz::ENTITY_TYPE,
                'is_active'       => true,
                'title'           => 'quiz::phrase.quiz_type',
                'description'     => 'added_a_quiz',
                'is_system'       => 0,
                'can_comment'     => true,
                'can_like'        => true,
                'can_share'       => true,
                'can_edit'        => false,
                'can_create_feed' => true,
            ],
        ];
    }

    public function getUserPrivacy(): array
    {
        return [
            'quiz.share_quizzes'       => [
                'phrase' => 'quiz::phrase.user_privacy.who_can_share_quizzes',
            ],
            'quiz.view_browse_quizzes' => [
                'phrase' => 'quiz::phrase.user_privacy.who_can_view_browse_quizzes',
            ],
        ];
    }

    public function getUserPrivacyResource(): array
    {
        return [
            'page'  => [
                'quiz.share_quizzes',
                'quiz.view_browse_quizzes',
            ],
            'group' => [
                'quiz.share_quizzes',
            ],
        ];
    }

    public function getDefaultPrivacy(): array
    {
        return [
            Quiz::ENTITY_TYPE => [
                'phrase'  => 'quiz::phrase.quizzes',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getProfileMenu(): array
    {
        return [
            Quiz::ENTITY_TYPE => [
                'phrase'  => 'quiz::phrase.quizzes',
                'default' => MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    public function getPolicies(): array
    {
        return [
            Quiz::class => QuizPolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Quiz::ENTITY_TYPE => [
                'view'                        => UserRole::LEVEL_GUEST,
                'create'                      => UserRole::LEVEL_REGISTERED,
                'update'                      => UserRole::LEVEL_REGISTERED,
                'delete'                      => UserRole::LEVEL_REGISTERED,
                'moderate'                    => UserRole::LEVEL_STAFF,
                'feature'                     => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'purchase_feature'            => [
                    'roles'     => UserRole::LEVEL_PAGE,
                    'is_public' => false,
                ],
                'approve'                     => UserRole::LEVEL_STAFF,
                'auto_approved'               => UserRole::LEVEL_REGISTERED,
                'play'                        => UserRole::LEVEL_REGISTERED,
                'save'                        => UserRole::LEVEL_REGISTERED,
                'report'                      => UserRole::LEVEL_REGISTERED,
                'like'                        => UserRole::LEVEL_REGISTERED,
                'share'                       => UserRole::LEVEL_REGISTERED,
                'comment'                     => UserRole::LEVEL_REGISTERED,
                'upload_photo_form'           => UserRole::LEVEL_REGISTERED,
                'require_upload_photo'        => UserRole::LEVEL_REGISTERED,
                'view_answers'                => UserRole::LEVEL_REGISTERED,
                'sponsor'                     => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'sponsor_free'                => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'sponsor_in_feed'             => [
                    'roles'     => UserRole::LEVEL_REGISTERED,
                    'is_public' => false,
                ],
                'auto_publish_sponsored_item' => [
                    'roles'     => UserRole::LEVEL_ADMINISTRATOR,
                    'is_public' => false,
                ],
                'download_attachment'         => UserRole::LEVEL_REGISTERED,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [
            'min_length_quiz_question'             => ['value' => 3],
            'max_length_quiz_question'             => ['value' => 100],
            'minimum_name_length'                  => ['value' => 3],
            'maximum_name_length'                  => ['value' => 255],
            'minimum_quiz_answer_length'           => ['value' => 3],
            'maximum_quiz_answer_length'           => ['value' => 255],
            'show_success_as_percentage_in_result' => ['value' => true],
            'quiz.purchase_sponsor_price'          => [
                'value'     => '',
                'is_public' => false,
            ],
        ];
    }

    public function getEvents(): array
    {
        return [
            'like.notification_to_callback_message'    => [
                LikeNotificationMessageListener::class,
            ],
            'comment.notification_to_callback_message' => [
                CommentNotificationMessageListener::class,
            ],
            'user.deleted'                             => [
                UserDeletedListener::class,
            ],
            'core.collect_total_items_stat'            => [
                CollectTotalItemsStatListener::class,
            ],
            'advertise.sponsor.enable_sponsor_feed'    => [
                EnableSponsorFeedListener::class,
            ],
            'advertise.sponsor.disable_sponsor_feed'   => [
                DisableSponsorFeedListener::class,
            ],
            'activity.update_feed_item_privacy'        => [
                UpdateFeedItemPrivacyListener::class,
            ],
            'activity.feed.deleted'                    => [
                FeedDeletedListener::class,
            ],
        ];
    }

    public function getNotificationTypes(): array
    {
        return [
            [
                'type'       => 'quiz_approve_notification',
                'module_id'  => 'quiz',
                'handler'    => QuizApproveNotifications::class,
                'title'      => 'quiz::phrase.quiz_approve_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 19,
            ],
            [
                'type'       => 'quiz_result_submitted_notification',
                'module_id'  => 'quiz',
                'handler'    => SubmitResultNotifications::class,
                'title'      => 'quiz::phrase.quiz_result_submitted_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 20,
            ],
            [
                'type'       => 'quiz_resubmit_notification',
                'module_id'  => 'quiz',
                'handler'    => QuizResubmitNotifications::class,
                'title'      => 'quiz::phrase.quiz_resubmit_notification_type',
                'is_request' => 0,
                'is_system'  => 1,
                'can_edit'   => 1,
                'channels'   => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'   => 21,
            ],
            [
                'type'              => 'quiz_follower_notification',
                'module_id'         => 'quiz',
                'require_module_id' => 'follow',
                'handler'           => NewQuizToFollowerNotification::class,
                'title'             => 'quiz::phrase.quiz_follower_notification_type',
                'is_request'        => 0,
                'is_system'         => 1,
                'can_edit'          => 1,
                'channels'          => ['mail', 'database', 'mobilepush', 'webpush'],
                'ordering'          => 21,
            ],
        ];
    }

    public function getUserValuePermissions(): array
    {
        return [
            Quiz::ENTITY_TYPE => [
                'flood_control'                                       => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control'                                       => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control_' . MetaFoxConstant::TIMEFRAME_DAILY   => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'action'  => 'quota_control',
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'quota_control_' . MetaFoxConstant::TIMEFRAME_MONTHLY => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 0,
                    'action'  => 'quota_control',
                    'roles'   => [
                        UserRole::ADMIN_USER  => 0,
                        UserRole::STAFF_USER  => 0,
                        UserRole::NORMAL_USER => 0,
                    ],
                ],
                'max_question_quiz'                                   => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 1,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 1,
                        UserRole::STAFF_USER  => 1,
                        UserRole::NORMAL_USER => 1,
                    ],
                    'extra'   => [
                        'fieldCreator' => [EditPermissionListener::class, 'maxQuestionPerQuiz'],
                    ],
                ],
                'min_question_quiz'                                   => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 1,
                    'roles'   => [
                        UserRole::ADMIN_USER  => 1,
                        UserRole::STAFF_USER  => 1,
                        UserRole::NORMAL_USER => 1,
                    ],
                    'extra'   => [
                        'fieldCreator' => [EditPermissionListener::class, 'minQuestionPerQuiz'],
                    ],
                ],
                'number_of_answers_per_default'                       => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 4,
                    'extra'   => [
                        'fieldCreator' => [EditPermissionListener::class, 'defaultAnswerPerQuiz'],
                    ],
                ],
                'max_answer_question_quiz'                            => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 4,
                    'extra'   => [
                        'fieldCreator' => [EditPermissionListener::class, 'maxAnswerPerQuiz'],
                    ],
                ],
                'min_answer_question_quiz'                            => [
                    'type'    => MetaFoxDataType::INTEGER,
                    'default' => 2,
                    'extra'   => [
                        'fieldCreator' => [EditPermissionListener::class, 'minAnswerPerQuiz'],
                    ],
                ],
            ],
        ];
    }

    public function getSiteStatContent(): ?array
    {
        return [
            Quiz::ENTITY_TYPE => ['icon' => 'ico-question-circle-o'],
            'pending_quiz'    => [
                'icon' => 'ico-clock-o',
                'to'   => '/quiz/quiz/browse?view=' . Browse::VIEW_PENDING,
            ],
        ];
    }

    public function getSavedTypes(): array
    {
        return [
            [
                'label' => __p('quiz::phrase.quizzes'),
                'value' => 'quiz',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getSitemap(): array
    {
        return ['quiz'];
    }

    /**
     * @return array<int, mixed>
     */
    public function getAdMobPages(): array
    {
        return [
            [
                'path' => '/quiz',
                'name' => 'quiz::phrase.ad_mob_quiz_home_page',
            ],
            [
                'path' => '/quiz/:id',
                'name' => 'quiz::phrase.ad_mob_quiz_detail_page',
            ],
        ];
    }
}
