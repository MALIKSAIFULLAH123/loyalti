<?php

namespace MetaFox\Music\Contracts;

interface SupportInterface
{
    /**
     * @return string
     */
    public function getDefaultSearchEntityType(): string;

    /**
     * @return array
     */
    public function getEntityTypeOptions(): array;

    /**
     * @param  string $entityType
     * @return string
     */
    public function convertEntityType(string $entityType): string;
}
