<?php

namespace MetaFox\Platform\Contracts;

interface PackageSettingListenerInterface
{
    /**
     * Use MetaFox drivers feature prefer than this method.
     *
     * @return array<string, string>
     * @deprecated
     */
    public function getPolicies(): array;

    /**
     * Use MetaFox drivers feature prefer than this method.
     *
     * @return array<string, string>
     * @deprecated
     */
    public function getPolicyHandlers(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getActivityTypes(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSavedTypes(): array;

    /**
     * @return array<string, mixed>
     */
    public function getActivityForm(): array;

    /**
     * Migrate to MetaFox drivers.
     * @return array<int, array<string, mixed>>
     * @deprecated
     */
    public function getNotificationTypes(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSearchTypes(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getUserPermissions(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getUserValuePermissions(): array;

    /**
     * @return array<string, mixed>
     */
    public function getSiteSettings(): array;

    /**
     * @return array<string, mixed>
     */
    public function getEvents(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getUserPrivacy(): array;

    /**
     * @return array<string, mixed>
     */
    public function getUserPrivacyResource(): array;

    /**
     * Define default privacy of app resource.
     * [ 'phrase' => 'module', 'default' => 0].
     *
     * @return array<string, mixed>
     */
    public function getDefaultPrivacy(): array;

    /**
     * Define profile menu.
     *[
     * 'entity' => [
     *      'phrase' => 'menu_module',
     *      'url'    => 'photo',
     *      'icon'   => 'photo',
     *      ],
     * ],.
     * Migrate to profile menu instead.
     *
     * @return array<string, mixed>
     * @deprecated
     */
    public function getProfileMenu(): array;

    /**
     * This will return an instance of AppSettingInterface or null.
     *
     * @return string|null|string[]
     */
    public function getAppSettings();

    /**
     * @param string $action
     *
     * @return mixed
     */
    public function handle(string $action);

    /**
     * @return array<string, mixed>
     */
    public function getUserValues(): array;

    /**
     * Prefer MetaFox Drivers feature.
     * @return string[]
     * @deprecated
     */
    public function getItemTypes(): array;
}
