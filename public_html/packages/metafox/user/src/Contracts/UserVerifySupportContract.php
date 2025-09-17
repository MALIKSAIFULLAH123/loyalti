<?php

namespace MetaFox\User\Contracts;

/**
 * Interface UserVerifySupportContract.
 */
interface UserVerifySupportContract
{
    /**
     * @param string $action
     *
     * @return string
     */
    public function getVerifyAtField(string $action): string;

    /**
     * @param string $action
     *
     * @return string
     */
    public function getVerifiableField(string $action): string;

    /**
     * @return array
     */
    public function getAllowedActions(string $service): array;
}
