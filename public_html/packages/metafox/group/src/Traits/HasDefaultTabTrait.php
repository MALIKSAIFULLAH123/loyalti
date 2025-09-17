<?php

namespace MetaFox\Group\Traits;

use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\IntegratedModule;
use MetaFox\Group\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Group\Support\Support;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxConstant;

trait HasDefaultTabTrait
{
    use CheckModeratorSettingTrait;

    public function getDefaultTabMenu(User $user, Group $group): string
    {
        $defaultActiveTabMenu = $group->landing_page;

        if ((!$group->isMember($user) && !$group->isPublicPrivacy()) || !$group->isApproved()) {
            return Support::DEFAULT_TAB_ABOUT;
        }

        return localCacheStore()->rememberForever(
            GroupFacade::getCacheKeyDefaultTabActive($group),
            function () use ($defaultActiveTabMenu, $group) {
                $menus = $this->integratedRepository()->getModulesActive($group->entityId());
                $item  = $menus->firstWhere('name', $defaultActiveTabMenu);

                return $item instanceof IntegratedModule
                    ? $item->tab ?? $defaultActiveTabMenu
                    : Support::DEFAULT_TAB_ABOUT;
            }
        );
    }

    public function getDefaultContentTab(User $user, Group $group): string
    {
        $defaultActiveTabContent = Support::YOUR_CONTENT_PENDING_TAB;

        $groupPolicy = PolicyGate::getPolicyFor(Group::class);

        $canManagePendingPosts = $groupPolicy->viewMyFeedContent($user, $group, MetaFoxConstant::ITEM_STATUS_PENDING);

        if (!$canManagePendingPosts) {
            $defaultActiveTabContent = Support::YOUR_CONTENT_PUBLISHED_TAB;
        }

        return $defaultActiveTabContent;
    }

    /**
     * @param User  $user
     * @param Group $group
     *
     * @return string|null
     */
    public function getDefaultTabManage(User $user, Group $group): ?string
    {
        $profileMenus = resolve(MenuItemRepositoryInterface::class)
            ->getMenuItemByMenuName(Support::GROUP_MANAGE_MENU, 'web', true);

        $tabPendingPost = $profileMenus->firstWhere('name', Support::MANAGE_PENDING_POST);

        if ($group->isAdmin($user) || $user->hasPermissionTo('group.moderate')) {
            return $tabPendingPost ? Support::MANAGE_PENDING_POST
                : Support::MANAGE_MEMBERSHIP_QUESTION;
        }

        if (!$group->isModerator($user)) {
            return null;
        }

        $tabManageReport = $profileMenus->firstWhere('name', Support::MANAGE_REPORTED_CONTENT_MENU_NAME);

        if (!$this->checkModeratorSetting($user, $group, 'approve_or_deny_post')) {
            if (!$this->checkModeratorSetting($user, $group, 'remove_post_and_comment_on_post')) {
                return null;
            }

            return $tabManageReport
                ? Support::MANAGE_REPORTED_CONTENT
                : null;
        }

        if ($tabPendingPost) {
            return Support::MANAGE_PENDING_POST;
        }

        return $tabManageReport ? Support::MANAGE_REPORTED_CONTENT : null;
    }

    /**
     * @return IntegratedModuleRepositoryInterface
     */
    private function integratedRepository(): IntegratedModuleRepositoryInterface
    {
        return resolve(IntegratedModuleRepositoryInterface::class);
    }
}
