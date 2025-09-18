<?php

namespace MetaFox\Page\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Page\Http\Resources\v1\Page\Admin\PageItemCollection;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\PageAdminRepositoryInterface;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewAdminScope;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\User as UserSupport;
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
        $module = 'page';
        $vars = [
            'page.default_item_privacy',
            'page.admin_in_charge_of_page_claims',
            'page.display_profile_photo_within_gallery',
            'page.display_cover_photo_within_gallery',
            'page.default_category',
            'page.minimum_name_length',
            'page.maximum_name_length',
        ];

        $value = [];

        foreach ($vars as $var) {
            if ($var != 'page.admin_in_charge_of_page_claims') {
                Arr::set($value, $var, Settings::get($var));
                continue;
            }

            $adminPageClaims = Settings::get($var, []);

            if (is_array($adminPageClaims)) {
                $adminPageClaims = array_unique($adminPageClaims, SORT_NUMERIC);
            }

            Arr::set($value, $var, Settings::get($var, $adminPageClaims));
        }

        $this->setFollowPageValue($value);

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function setFollowPageValue(array &$value): void
    {
        $pageIds = Settings::get('page.auto_follow_pages_on_signup');

        if (!is_array($pageIds) || !count($pageIds)) {
            return;
        }

        $pages = $this->getPageAdminRepository()->getPagesByPageIds($pageIds);
        $pageCollections = (new PageItemCollection($pages))->toArray(request());

        Arr::set(
            $value,
            'page.auto_follow_pages_on_signup',
            $pageCollections
        );
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        $users = UserSupport::getUsersByRoleId(UserRole::ADMIN_USER);
        $categories = $this->getCategoryRepository()->getCategoriesForForm();

        $admins = [];
        if (null != $users) {
            $users = $users->map(function (User $user) {
                return [
                    'label' => $user->display_name,
                    'value' => $user->entityId(),
                ];
            })->toArray();
            $admins = array_merge($admins, $users);
        }

        if (empty($admins)) {
            $admins = [['label' => __p('core::phrase.none'), 'value' => 0]];
        }

        $basic->addFields(
            Builder::choice('page.admin_in_charge_of_page_claims')
                ->label(__p('page::admin.admin_in_charge_of_page_claims'))
                ->description(__p('page::admin.admin_in_charge_of_page_claims_description'))
                ->disableClearable()
                ->multiple(true)
                ->options($admins),
            Builder::friendPicker('page.auto_follow_pages_on_signup')
                ->multiple()
                ->apiEndpoint('admincp/page')
                ->setAttribute('apiParams', [
                    'view' => ViewAdminScope::VIEW_APPROVED
                ])
                ->setAttribute('noOptionsText', __p('page::web.no_pages_found'))
                ->label(__p('page::admin.auto_follow_pages_on_signup_label'))
                ->description(__p('page::admin.auto_follow_pages_on_signup_desc')),
            Builder::text('page.minimum_name_length')
                ->required()
                ->asNumber()
                ->label(__p('page::admin.minimum_name_length'))
                ->yup(
                    Yup::number()
                        ->required(__p('page::validation.minimum_name_length_description_required', ['min' => 1]))
                        ->unint()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('page.maximum_name_length')
                ->required()
                ->asNumber()
                ->label(__p('page::admin.maximum_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()
                        ->required(__p(
                            'page::validation.maximum_name_length_description_required',
                            ['max' => $maximumNameLength]
                        ))
                        ->unint()
                        ->max($maximumNameLength)
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->unint()
                                        ->min(['ref' => 'minimum_name_length'])
                                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::switch('page.display_profile_photo_within_gallery')
                ->label(__p('page::admin.display_profile_photo_within_gallery'))
                ->description(__p('page::admin.display_profile_photo_within_gallery_description')),
            Builder::switch('page.display_cover_photo_within_gallery')
                ->label(__p('page::admin.display_cover_photo_within_gallery'))
                ->description(__p('page::admin.display_cover_photo_within_gallery_description')),
            Builder::choice('page.default_item_privacy')
                ->label(__p('page::admin.default_item_privacy'))
                ->description(__p('page::admin.default_item_privacy_description'))
                ->required()
                ->options([
                    [
                        'label' => __p('phrase.user_privacy.anyone'),
                        'value' => MetaFoxPrivacy::EVERYONE,
                    ],
                    [
                        'label' => __p('phrase.user_privacy.members_only'),
                        'value' => MetaFoxPrivacy::FRIENDS,
                    ],
                    [
                        'label' => __p('phrase.user_privacy.admins_only'),
                        'value' => MetaFoxPrivacy::CUSTOM,
                    ],
                ]),
            Builder::choice('page.default_category')
                ->label(__p('page::admin.page_default_category'))
                ->description(__p('page::admin.page_default_category_description'))
                ->disableClearable()
                ->required()
                ->options($categories),
        );

        $this->addDefaultFooter(true);
    }

    protected function getCategoryRepository(): PageCategoryRepositoryInterface
    {
        return resolve(PageCategoryRepositoryInterface::class);
    }

    protected function getPageAdminRepository(): PageAdminRepositoryInterface
    {
        return resolve(PageAdminRepositoryInterface::class);
    }

    public function validated(Request $request): array
    {
        $params = $request->validate([
            'page.default_item_privacy'                 => 'sometimes|nullable|numeric',
            'page.admin_in_charge_of_page_claims'       => 'sometimes|array',
            'page.display_profile_photo_within_gallery' => 'boolean',
            'page.display_cover_photo_within_gallery'   => 'boolean',
            'page.default_category'                     => 'sometimes|numeric|exists:page_categories,id',
            'page.minimum_name_length'                  => 'sometimes|nullable|integer',
            'page.maximum_name_length'                  => 'sometimes|nullable|numeric',
            'page.auto_follow_pages_on_signup'          => 'sometimes|nullable|array',
        ]);

        $adminPageClaims = Arr::get($params, 'page.admin_in_charge_of_page_claims', []);
        if ($adminPageClaims) {
            $adminPageClaims = array_unique($adminPageClaims, SORT_NUMERIC);
        }

        Arr::set($params, 'page.admin_in_charge_of_page_claims', $adminPageClaims);

        Arr::set(
            $params,
            'page.auto_follow_pages_on_signup',
            $this->extractFollowPageIds($params)
        );

        return $params;
    }

    protected function extractFollowPageIds(array $params): array
    {
        $pages = Arr::get($params, 'page.auto_follow_pages_on_signup', []);

        if (!is_array($pages) || empty($pages)) {
            return [];
        }

        return Arr::pluck($pages, 'id');
    }
}
