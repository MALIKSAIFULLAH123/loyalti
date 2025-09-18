<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\InfoSettingRepositoryInterface;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Facade\Page as PageFacade;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * Class InfoSettingRepository.
 * @method Page getModel()
 * @method Page find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @inore
 */
class InfoSettingRepository extends AbstractRepository implements InfoSettingRepositoryInterface
{
    public function model(): string
    {
        return Page::class;
    }

    protected function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     *
     * @param User $context
     * @param int  $pageId
     *
     * @return array
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function getInfoSettings(User $context, int $pageId): array
    {
        $page = $this->find($pageId);
        policy_authorize(PagePolicy::class, 'update', $context, $page);

        $resourceData = [];

        $settings = [
            'name'          => ban_word()->clean($page->name),
            'category_id'   => $page->category_id,
            'profile_name'  => $page->profile_name,
            'landing_page'  => $page->landing_page ?? 'home',
            'external_link' => $page->external_link,
        ];

        $fieldsName = CustomFieldFacade::loadFieldName(user(), [
            'section_type' => CustomField::SECTION_TYPE_PAGE,
            'view'         => CustomField::VIEW_ALL,
        ]);

        if (!empty($fieldsName)) {
            $settings['additional_information'] = null;
        }

        return $this->handleSettings($page, $settings, Page::ORDERING_FOR_INFO_SETTING, $resourceData);
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function getAboutSettings(User $context, int $pageId): array
    {
        $page = $this->find($pageId);

        policy_authorize(PagePolicy::class, 'update', $context, $page);

        $resourceData = [];

        $settings = [
            'text'     => $page?->pageText?->text,
            'location' => $page->location_name,
        ];

        return $this->handleSettings($page, $settings, Page::ORDERING_FOR_ABOUT_SETTING, $resourceData);
    }

    /**
     * @param Page  $page
     * @param array $settings
     * @param array $ordering
     * @param array $resourceData
     *
     * @return array
     */
    protected function handleSettings(Page $page, array $settings, array $ordering, array $resourceData = []): array
    {
        unset($settings['summary']);
        $resolution = MetaFox::getResolution();
        $actions    = $this->getSettingActions();

        foreach ($settings as $key => $value) {
            if (!in_array($key, PageFacade::getInfoSettingsSupportByResolution($resolution))) {
                continue;
            }

            $data = [
                'label'       => __p("page::phrase.label.{$key}"),
                'value'       => $value,
                'action'      => Arr::get($actions, $key),
                'ordering'    => Arr::get($ordering, $key, 0),
                'description' => '',
                'name'        => $key,
                'id'          => $page->entityId(),
            ];

            $methodName = 'get' . Str::studly($key) . 'Setting';
            if (method_exists($this, $methodName)) {
                $resourceData[] = $this->$methodName($page, $data);
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
            'external_link'          => 'getExternalLinkForm',
        ];
    }

    protected function getProfileNameSetting(Page $page, array $data): array
    {
        $data['name']  = 'vanity_url';
        $data['value'] = $page->profile_name == MetaFoxConstant::EMPTY_STRING
            ? "page/{$page->entityId()}"
            : $page->profile_name;

        $data['contextualDescription'] = url_utility()->makeApiFullUrl('');
        $data['defaultValue']          = url_utility()->makeApiFullUrl("page/{$page->entityId()}");

        return $data;
    }

    protected function getExternalLinkSetting(Page $page, array $data): array
    {
        $data['type'] = MetaFoxForm::TEXT;

        return $data;
    }

    protected function getLandingPageSetting(Page $page, array $data): array
    {
        $data['options'] = $this->pageRepository()->getProfileMenus($page->entityId());
        $data['type']    = MetaFoxForm::COMPONENT_SELECT;

        return $data;
    }

    protected function getCategoryIdSetting(Page $page, array $data): array
    {
        /** @var PageCategoryRepositoryInterface $categoryRepository */
        $categoryRepository = resolve(PageCategoryRepositoryInterface::class);
        $options            = $categoryRepository->getCategoriesForUpdateForm(collect([$page->category]));

        $data['name']    = 'category_id';
        $data['options'] = $options;
        $data['type']    = MetaFoxForm::COMPONENT_SELECT;

        return $data;
    }

    protected function getLocationSetting(Page $page, array $data): array
    {
        $data['type']  = MetaFoxForm::LOCATION;

        $data['value'] = [
            'address' => $page->location_name,
            'lat'     => $page->location_latitude,
            'lng'     => $page->location_longitude,
            'full_address' => $page->location_address,
        ];

        return $data;
    }

    protected function getTextSetting(Page $page, array $data): array
    {
        $pageText = $page->pageText;

        $description = MetaFoxConstant::EMPTY_STRING;

        if ($pageText instanceof ResourceText) {
            $description = parse_output()->parseItemDescription($pageText->text_parsed);
        }

        $data['type'] = \MetaFox\Page\Support\Facade\Page::allowHtmlOnDescription() ? MetaFoxForm::RICH_TEXT_EDITOR : MetaFoxForm::TEXT_AREA;

        if (MetaFox::isMobile()) {
            $data['type'] = MetaFoxForm::TEXT_AREA;
        }

        $data['value'] = $description;

        return $data;
    }
}
