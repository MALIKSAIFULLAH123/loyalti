<?php

namespace MetaFox\HealthCheck\Contracts;

interface NoticeManager
{
    /**
     * collect reports from checkers.
     * @return array<mixed>
     */
    public function collectReports(): array;
}
