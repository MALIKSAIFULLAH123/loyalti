<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\InfoSettingRepositoryInterface;
use MetaFox\Group\Support\Facades\Group as GroupFacades;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * Class InfoSettingRepository.
 * @method Group getModel()
 * @method Group find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @inore
 */
class InfoSettingRepository extends AbstractRepository implements InfoSettingRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use CollectTotalItemStatTrait;

    public function model(): string
    {
        return Group::class;
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @param User $context
     * @param int  $groupId
     *
     * @return array
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function getInfoSettingsGroup(User $context, int $groupId): array
    {
        $group = $this->find($groupId);
        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        $ordering     = Group::ORDERING_FOR_INFO_SETTING;
        $resourceData = [];
        $settings     = [
            'name'         => $group->toTitle(),
            'category_id'  => $group->category_id,
            'profile_name' => $group->profile_name,
            'landing_page' => $group->landing_page,
            'privacy_type' => $group->privacy_type,
        ];

        $fieldsName = CustomFieldFacade::loadFieldName(user(), [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'view'         => CustomField::VIEW_ALL,
        ]);

        if (!empty($fieldsName)) {
            $settings['additional_information'] = null;
        }

        return $this->handleSettings($group, $settings, $ordering, $resourceData);
    }

    /**
     * @param User $context
     * @param int  $groupId
     *
     * @return array
     * @throws AuthorizationException
     */
    public function getAboutSettingsGroup(User $context, int $groupId): array
    {
        $group = $this->find($groupId);
        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        $ordering     = Group::ORDERING_FOR_ABOUT_SETTING;
        $resourceData = [];
        $settings     = [
            'text'     => $group?->groupText?->text,
            'location' => $group->location_name,
        ];

        return $this->handleSettings($group, $settings, $ordering, $resourceData);
    }

    /**
     * @param Group $group
     * @param array $settings
     * @param array $ordering
     * @param array $resourceData
     *
     * @return array
     */
    protected function handleSettings(Group $group, array $settings, array $ordering, array $resourceData = []): array
    {
        $resolution = MetaFox::getResolution();
        $actions    = $this->getSettingActions();

        foreach ($settings as $key => $value) {
            if (!in_array($key, GroupFacades::getInfoSettingsSupportByResolution($resolution))) {
                continue;
            }

            $data = [
                'label'       => __p("group::phrase.label.{$key}"),
                'value'       => $value,
                'action'      => Arr::get($actions, $key),
                'ordering'    => Arr::get($ordering, $key, 0),
                'description' => '',
                'name'        => $key,
                'id'          => $group->entityId(),
            ];

            $methodName = 'get' . Str::studly($key) . 'Setting';
            if (method_exists($this, $methodName)) {
                $resourceData[] = $this->$methodName($group, $data);
                continue;
            }

            $resourceData[] = $data;
        }

        return $resourceData;
    }

    protected function getSettingActions(): array
    {
        return [
            'name'                   => 'getNameSettingForm',
            'category_id'            => 'getCategorySettingForm',
            'landing_page'           => 'getLandingPageSettingForm',
            'profile_name'           => 'getProfileNameSettingForm',
            'privacy_type'           => 'getPrivacyTypeSettingForm',
            'additional_information' => 'getAdditionalInformationSettingFrom',
            'text'                   => 'getTextSettingFrom',
            'location'               => 'getLocationSettingForm',
        ];
    }

    protected function getProfileNameSetting(Group $group, array $data): array
    {
        $data['name']  = 'vanity_url';
        $data['value'] = $group->profile_name == MetaFoxConstant::EMPTY_STRING
            ? "group/{$group->entityId()}"
            : $group->profile_name;

        $data['contextualDescription'] = url_utility()->makeApiFullUrl('');
        $data['defaultValue']          = url_utility()->makeApiFullUrl("group/{$group->entityId()}");

        return $data;
    }

    protected function getPrivacyTypeSetting(Group $group, array $data): array
    {
        $data['type']        = MetaFoxForm::RADIO_GROUP;
        $data['name']        = 'reg_method';
        $data['options']     = [
            [
                'value'       => PrivacyTypeHandler::PUBLIC,
                'label'       => __p(PrivacyTypeHandler::PRIVACY_PHRASE[PrivacyTypeHandler::PUBLIC]),
                'description' => __p('group::phrase.anyone_can_see_the_group_its_members_and_their_posts'),
            ], [
                'value'       => PrivacyTypeHandler::CLOSED,
                'label'       => __p(PrivacyTypeHandler::PRIVACY_PHRASE[PrivacyTypeHandler::CLOSED]),
                'description' => __p('group::phrase.anyone_can_find_the_group_and_see_who_s_in_it_only_members_can_see_posts'),
            ], [
                'value'       => PrivacyTypeHandler::SECRET,
                'label'       => __p(PrivacyTypeHandler::PRIVACY_PHRASE[PrivacyTypeHandler::SECRET]),
                'description' => __p('group::phrase.only_members_can_find_the_group_and_see_posts'),
            ],
        ];
        $data['description'] = __p('group::phrase.description.privacy_type');

        return $data;
    }

    protected function getLandingPageSetting(Group $group, array $data): array
    {
        $data['options'] = $this->groupRepository()->getProfileMenus($group->entityId());
        $data['type']    = MetaFoxForm::COMPONENT_SELECT;

        return $data;
    }

    protected function getCategoryIdSetting(Group $group, array $data): array
    {
        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = resolve(CategoryRepositoryInterface::class);
        $options            = $categoryRepository->getCategoriesForUpdateForm(collect([$group->category]));

        $data['name']    = 'category_id';
        $data['options'] = $options;
        $data['type']    = MetaFoxForm::COMPONENT_SELECT;

        return $data;
    }

    protected function getLocationSetting(Group $group, array $data): array
    {
        $data['type']  = MetaFoxForm::LOCATION;

        $data['value'] = [
            'full_address' => $group->location_address,
            'address'      => $group->location_name,
            'lat'          => $group->location_latitude,
            'lng'          => $group->location_longitude,
        ];

        return $data;
    }

    protected function getTextSetting(Group $group, array $data): array
    {
        $groupText   = $group->groupText;
        $description = MetaFoxConstant::EMPTY_STRING;

        if ($groupText instanceof ResourceText) {
            $description = parse_output()->parseItemDescription($groupText->text_parsed);
        }

        $data['type'] = MetaFoxForm::RICH_TEXT_EDITOR;

        if (MetaFox::isMobile()) {
            $data['type'] = MetaFoxForm::TEXT_AREA;
        }

        $data['value'] = $description;
        $data['name']  = 'text';

        return $data;
    }
}
