<?php

namespace MetaFox\Group\Support;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use MetaFox\Group\Contracts\SupportContract;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Policies\MemberPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Group\SortScope as GroupSortScope;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

class Support implements SupportContract
{

    private GroupRepositoryInterface $groupRepository;

    private QuestionRepositoryInterface         $questionRepository;
    private IntegratedModuleRepositoryInterface $integratedModuleRepository;

    public const MENTION_REGEX = '^\[group=(.*?)\]^';

    public const SHARED_TYPE                       = 'group';
    public const GROUP_MANAGE_MENU                 = 'group.groupManagerMenu';
    public const MANAGE_PENDING_POST               = 'pending_posts';
    public const MANAGE_MEMBERSHIP_QUESTION        = 'membership_questions';
    public const MANAGE_REPORTED_CONTENT           = 'report';
    public const MANAGE_REPORTED_CONTENT_MENU_NAME = 'member-reported_content';

    public const DEFAULT_TAB_ABOUT = 'about';

    public const YOUR_CONTENT_PENDING_TAB   = 'pending';
    public const YOUR_CONTENT_PUBLISHED_TAB = 'published';
    public const UPDATE_GROUP_NAME_TYPE     = 'update_group_name';
    public const UPDATE_GROUP_PRIVACY_TYPE  = 'update_group_privacy';
    public const TAB_NAME_DEFAULTS          = ['about'];

    public function __construct(
        GroupRepositoryInterface            $groupRepository,
        QuestionRepositoryInterface         $questionRepository,
        IntegratedModuleRepositoryInterface $integratedModuleRepository
    )
    {
        $this->groupRepository            = $groupRepository;
        $this->questionRepository         = $questionRepository;
        $this->integratedModuleRepository = $integratedModuleRepository;
    }

    public function getGroup(int $id): ?Group
    {
        return $this->groupRepository->getGroup($id);
    }

    public function mustAnswerMembershipQuestion(Group $group): bool
    {
        return $this->groupRepository->hasGroupQuestions($group) && $group->is_answer_membership_question;
    }

    public function mustAcceptGroupRule(Group $group): bool
    {
        return $this->groupRepository->hasGroupRule($group) && $group->is_rule_confirmation;
    }

    public function getQuestions(Group $group): ?Collection
    {
        return $this->questionRepository->getQuestionsForForm($group->entityId());
    }

    public function getMaximumMembershipQuestion(): int
    {
        return (int) Settings::get('group.maximum_membership_question', 3);
    }

    public function getMaximumNumberMembershipQuestionOption(): int
    {
        return (int) Settings::get('group.maximum_membership_question_option', 5);
    }

    /**
     * getListTypes.
     *
     * @return array<mixed>
     */
    public function getListTypes(): array
    {
        return Cache::rememberForever('groups_list_types', function () {
            $resourceName = 'feed_type';

            $integrationTypes = $this->getDefaultListTypes($resourceName);

            $menuItems = resolve(MenuItemRepositoryInterface::class)
                ->getMenuItemByMenuName(
                    'group.group.profileMenu',
                    'web',
                    true
                );

            if ($menuItems->count()) {
                foreach ($menuItems as $menuItem) {
                    if (is_string($menuItem->name)) {
                        $model = Relation::getMorphedModel($menuItem->name);

                        if (null !== $model) {
                            $model = resolve($model);
                        }

                        if ($model instanceof Content) {
                            $integrationTypes[] = [
                                'id'            => $model->entityType(),
                                'resource_name' => $resourceName,
                                'name'          => __p($menuItem->label),
                            ];
                        }
                    }
                }
            }

            return $integrationTypes;
        });
    }

    /**
     * getDefaultListTypes.
     *
     * @param string $resourceName
     *
     * @return array<mixed>
     */
    protected function getDefaultListTypes(string $resourceName): array
    {
        if (!app_active('metafox/activity')) {
            return [];
        }

        $types[] = [
            'id'            => 'all',
            'resource_name' => $resourceName,
            'name'          => __p('core::phrase.all'),
        ];

        $postModel = Relation::getMorphedModel('activity_post');

        $linkModel = Relation::getMorphedModel('link');

        if (null !== $postModel) {
            $postModel = resolve($postModel);

            $types[] = [
                'id'            => $postModel->entityType(),
                'resource_name' => $resourceName,
                'name'          => __p('activity::phrase.posts'),
            ];
        }

        if (null !== $linkModel) {
            $linkModel = resolve($linkModel);

            $types[] = [
                'id'            => $linkModel->entityType(),
                'resource_name' => $resourceName,
                'name'          => __p('core::phrase.links'),
            ];
        }

        return $types;
    }

    public function getPrivacyList(): array
    {
        return [
            // Member only
            [
                'privacy'         => MetaFoxPrivacy::FRIENDS,
                'privacy_type'    => Group::GROUP_MEMBERS,
                'privacy_icon'    => 'ico-user-two-men',
                'privacy_tooltip' => [
                    'var_name' => 'group::phrase.member_of_group_name',
                    'params'   => [
                        'name' => 'ownerEntity',
                    ],
                ],
            ],
            // Admin only.
            [
                'privacy'      => MetaFoxPrivacy::CUSTOM,
                'privacy_type' => Group::GROUP_ADMINS,
            ],
            // Moderator only.
            [
                'privacy'      => MetaFoxPrivacy::CUSTOM,
                'privacy_type' => Group::GROUP_MODERATORS,
            ],
        ];
    }

    public function getMentions(string $content): array
    {
        $userIds = [];

        try {
            preg_match_all(self::MENTION_REGEX, $content, $matches);
            $userIds = array_unique($matches[1]);
        } catch (Exception $e) {
            // Silent.
        }

        return $userIds;
    }

    public function getGroupsForMention(array $ids): Collection
    {
        $collection = $this->groupRepository->getModel()->newModelQuery()
            ->whereIn('id', $ids)
            ->get();

        return $collection->mapWithKeys(function ($group) {
            return [$group->entityId() => $group];
        });
    }

    public function getGroupBuilder(User $user): Builder
    {
        return $this->groupRepository->getGroupBuilder($user);
    }

    public function getPublicGroupBuilder(User $user): Builder
    {
        return $this->groupRepository->getPublicGroupBuilder($user);
    }

    /**
     * @inheritDoc
     */
    public function getMaximumNumberGroupRule(): int
    {
        return (int) Settings::get('group.maximum_number_group_rule', 3);
    }

    /**
     * @inheritDoc
     */
    public function isFollowing(ContractUser $context, ContractUser $user): bool
    {
        if (!app('events')->dispatch('follow.is_follow', [$context, $user], true)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getProfileMenuSettings(Group $group): array
    {
        return $this->integratedModuleRepository->getProfileMenuSettings($group->entityId());
    }

    public function getTabNameDefaults(Group $group): array
    {
        $data = self::TAB_NAME_DEFAULTS;

        if ($group->landing_page) {
            array_push($data, $group->landing_page);
        }

        return $data;
    }

    public function getAllowApiRules(): array
    {
        $apiRules = [
            'q'           => ['truthy', 'q'],
            'sort'        => ['includes', 'sort', GroupSortScope::getAllowSort()],
            'category_id' => ['truthy', 'category_id'], 'type_id' => ['truthy', 'type_id'],
            'is_featured' => ['truthy', 'is_featured'],
            'when'        => ['includes', 'when', WhenScope::getAllowWhen()],
            'view'        => [
                'includes', 'view', ViewScope::getWebViewAllActionOptions(),
            ],
        ];

        $fields = CustomFieldFacade::loadFieldName(Auth::user(), [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        if (empty($fields)) {
            return $apiRules;
        }

        foreach ($fields as $field) {
            $apiRules[$field] = ['truthy', $field];
        }

        return $apiRules;
    }

    public function getInfoSettingsSupportByResolution(string $resolution): array
    {
        $keys = [
            'name',
            'category_id',
            'profile_name',
            'text',
            'location',
            'additional_information',
            'landing_page',
        ];

        if ($resolution == MetaFoxConstant::RESOLUTION_WEB) {
            $keys = array_merge($keys, [
                'privacy_type',
            ]);
        }

        return $keys;
    }

    public function getTotalMemberByPrivacy(Group $group): int
    {
        $user = Auth::user();

        if (!$user || !policy_check(MemberPolicy::class, 'viewCount', $user, $group)) {
            return 0;
        }

        return $group->total_member;
    }

    public function getCacheKeyDefaultTabActive(Group $group): string
    {
        return sprintf(
            '%s::getDefaultTabMenu(%s:%s)',
            get_class($group),
            $group->entityType(),
            $group->entityId(),
        );
    }

    public function statusInviteInfo(int $statusId): array
    {
        $map = StatusScope::getStatusLabelMap();
        return match ($statusId) {
            Invite::STATUS_PENDING   => [
                'label' => $map[StatusScope::STATUS_PENDING],
                'color' => null,
            ],
            Invite::STATUS_APPROVED  => [
                'label' => $map[StatusScope::STATUS_ACCEPTED],
                'color' => null,
            ],
            Invite::STATUS_CANCELLED => [
                'label' => $map[StatusScope::STATUS_CANCELLED],
                'color' => null,
            ],
            Invite::STATUS_EXPIRED   => [
                'label' => $map[StatusScope::STATUS_EXPIRED],
                'color' => null,
            ],
            default                  => [
                'label' => $map[StatusScope::STATUS_DENIED],
                'color' => null,
            ],
        };
    }
}
