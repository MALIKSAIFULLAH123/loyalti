<?php

namespace MetaFox\Platform\Contracts;

/**
 * Interface AppSettingInterface.
 */
interface AppSettingInterface
{
    /**
     * @return array
     */
    public function getResources(): array;

    public function getModuleName(): ?string;

    public function getRealModuleName(): string;

    public function setContextUser(User $user): self;

    public function getContextUser(): User;

    public function getMainResource(): ?string;

    public function getHomeResource(): ?string;

    public function getCategoryResource(): ?string;

    public function getTypeResource(): ?string;

    public function getHomeView(): string;

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array;

    /**
     * @return array<string, mixed>
     */
    public function getAppSettings(): array;

    /**
     * @return array<string, mixed>
     */
    public function toSettings(): array;
}
