<?php

namespace MetaFox\User\Contracts\Support;

/**
 * Interface ActionServiceManagerInterface.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface ActionServiceManagerInterface
{
    /**
     * @param string $service
     * @param string $name
     *
     * @return ActionServiceInterface
     */
    public function get(string $service, string $name): ActionServiceInterface;

    /**
     * @param string $service
     *
     * @return ActionServiceInterface[]
     */
    public function getAllByService(string $service): array;
}
