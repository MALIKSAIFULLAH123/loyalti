<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface Invite.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
interface Invite
{
    /**
     * send contact information to the configured recipients.
     *
     * @param Model $model
     * @return void
     */
    public function sendMail(Model $model): void;

    /**
     * @param Model $model
     * @return void
     */
    public function sendSMS(Model $model): void;

    /**
     * @param Model $model
     * @return void
     */
    public function send(Model $model): void;

    /**
     * @return array
     */
    public function getStatusOptions(): array;

    /**
     * @param int $statusId
     * @return string
     */
    public function getStatusPhrase(int $statusId): string;

    /**
     * @param int $statusId
     * @return array
     */
    public function getStatusInfo(int $statusId): array;

    /**
     * @return array
     */
    public function getStatusRules(): array;
}
