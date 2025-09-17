<?php

namespace MetaFox\Newsletter\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Newsletter.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface NewsletterAdminRepositoryInterface
{
    /**
     * @return Newsletter|null
     */
    public function pickStartNewsletter(): ?Newsletter;

    /**
     * Create a Newsletter.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Newsletter
     */
    public function createNewsletter(User $context, array $attributes): Newsletter;

    /**
     * Get a Newsletter by ID.
     *
     * @param int $id
     *
     * @return Newsletter|null
     */
    public function getNewsletter(int $id): ?Newsletter;

    /**
     * Get a list of Newsletters.
     *
     * @param array $attributes (optional) Additional attributes for filtering.
     *
     * @return Paginator
     */
    public function getNewsletters(array $attributes = []): Paginator;

    /**
     * Update a Newsletter.
     *
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Newsletter
     */
    public function updateNewsletter(User $context, int $id, array $attributes): Newsletter;

    /**
     * Delete a Newsletter.
     *
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function deleteNewsletter(User $context, int $id): bool;

    /**
     * Get user IDs for a Newsletter.
     *
     * @param Newsletter $newsletter
     *
     * @return array
     */
    public function getUserIdsForNewsletter(Newsletter $newsletter): array;

    /**
     * Check if a Newsletter should be sent.
     *
     * @param ?Newsletter $newsletter
     *
     * @return bool
     */
    public function shouldSend(?Newsletter $newsletter): bool;

    /**
     * Chunk user IDs for a Newsletter.
     *
     * @param Newsletter $newsletter
     * @param array      $userIds
     *
     * @return array
     */
    public function sliceUserIds(Newsletter $newsletter, array $userIds): array;

    /**
     * Process a Newsletter.
     *
     * @param Newsletter $newsletter
     *
     * @return bool
     */
    public function processNewsletter(Newsletter $newsletter): bool;

    /**
     * Reprocess a Newsletter.
     *
     * @param Newsletter $newsletter
     *
     * @return bool
     */
    public function reprocessNewsletter(Newsletter $newsletter): bool;

    /**
     * Stop a Newsletter.
     *
     * @param Newsletter $newsletter
     *
     * @return bool
     */
    public function stopNewsletter(Newsletter $newsletter): bool;

    /**
     * Resend a Newsletter.
     *
     * @param Newsletter $newsletter
     *
     * @return bool
     */
    public function resendNewsletter(Newsletter $newsletter): bool;

    /**
     * @param Newsletter $newsletter
     *
     * @return void
     */
    public function updateTotalUser(Newsletter $newsletter): void;

    /**
     * @param Newsletter $newsletter
     * @param array      $userIds
     *
     * @return void
     */
    public function handleSendNewsletter(Newsletter $newsletter, array $userIds): void;

    /**
     * @param Newsletter $newsletter
     *
     * @return void
     */
    public function handleStatus(Newsletter $newsletter): void;

    /**
     * @param Newsletter $newsletter
     *
     * @return bool
     */
    public function handleArchive(Newsletter $newsletter): bool;

    /**
     * Transform attributes for a query.
     *
     * @param Newsletter $newsletter
     *
     * @return array
     */
    public function buildAttributesForQuery(Newsletter $newsletter): array;

    /**
     * @param array $recipients
     * @param int   $newsletterId
     *
     * @return Newsletter
     */
    public function sendTest(array $recipients, int $newsletterId): Newsletter;
}
