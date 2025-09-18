<?php

namespace MetaFox\Newsletter\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Newsletter\Models\NewsletterText;
use MetaFox\Newsletter\Notifications\NewsletterNotification;
use MetaFox\Newsletter\Policies\NewsletterPolicy;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Repositories\UserAdminRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\SortScope;
use MetaFox\User\Support\Browse\Scopes\User\ViewScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class NewsletterRepository.
 */
class NewsletterAdminRepository extends AbstractRepository implements NewsletterAdminRepositoryInterface
{
    public function model()
    {
        return Newsletter::class;
    }

    public function pickStartNewsletter(): ?Newsletter
    {
        return $this->getModel()
            ->newQuery()
            ->where('status', '=', Newsletter::PENDING_STATUS)
            ->orderBy('id')
            ->first();
    }

    public function createNewsletter(User $context, array $attributes): Newsletter
    {
        $attributesText = Arr::only($attributes, ['text', 'text_html']);
        Arr::except($attributes, ['text', 'text_html']);

        $attributes = array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'status'    => Newsletter::NOT_STARTED_STATUS,
        ]);

        $newsletter = new Newsletter();
        $newsletter->fill($attributes);
        $newsletter->save();

        Arr::set($attributesText, 'id', $newsletter->entityId());
        $newsletterText = new NewsletterText();
        $newsletterText->fill($attributesText);
        $newsletterText->save($attributesText);

        return $newsletter->refresh();
    }

    public function getNewsletter(int $id): ?Newsletter
    {
        return $this->getModel()->newQuery()->where('id', $id)->first();
    }

    public function getNewsletters(array $attributes = []): Paginator
    {
        return $this->getModel()->newQuery()
            ->orderByDesc('id')
            ->paginate($attributes['limit'] ?? 12);
    }

    public function updateNewsletter(User $context, int $id, array $attributes): Newsletter
    {
        $attributesText = Arr::only($attributes, ['text', 'text_html']);
        Arr::except($attributes, ['text', 'text_html']);

        $newsletter = $this->find($id);

        policy_authorize(NewsletterPolicy::class, 'update', $context, $newsletter);

        $newsletter->fill($attributes);

        $newsletter->save();

        $newsletterText = new NewsletterText();
        try {
            $newsletterText = $newsletterText->find($newsletter->entityId());
        } catch (\Exception $e) {
            Arr::set($attributesText, 'id', $newsletter->entityId());
        }

        $newsletterText->fill($attributesText);
        $newsletterText->save();

        return $newsletter;
    }

    public function deleteNewsletter(User $context, int $id): bool
    {
        $newsletter = $this->find($id);

        $newsletter->delete();

        return true;
    }

    public function getUserIdsForNewsletter(Newsletter $newsletter): array
    {
        return $this
            ->buildQueryForNewsletter($newsletter)
            ->where('id', '>', $newsletter->last_sent_id)
            ->pluck('id')
            ->toArray();
    }

    public function buildQueryForNewsletter(Newsletter $newsletter): Builder
    {
        $attributes = $this->buildAttributesForQuery($newsletter);

        return $this->userAdminRepository()
            ->buildQueryViewUsers($attributes)
            ->reorder('users.id');
    }

    public function buildAttributesForQuery(Newsletter $newsletter): array
    {
        $attributes = [];

        $attributes['view']      = ViewScope::VIEW_DEFAULT;
        $attributes['sort']      = SortScope::SORT_CREATED_AT;
        $attributes['sort_type'] = SortScope::SORT_TYPE_DEFAULT;

        $ageFrom = $newsletter->age_from;

        if ($ageFrom) {
            $minDate = Carbon::today()->subYears($ageFrom);
            Arr::set($attributes, 'age_from', $minDate);
        }

        $ageTo = $newsletter->age_to;

        if ($ageTo) {
            $maxDate = Carbon::today()->subYears($ageTo)->endOfDay();
            Arr::set($attributes, 'age_to', $maxDate);
        }

        if (!empty($newsletter->rolesIds())) {
            Arr::set($attributes, 'group', $newsletter->rolesIds());
        }

        if (!empty($newsletter->genderIds())) {
            Arr::set($attributes, 'gender', $newsletter->genderIds());
        }

        if (!empty($newsletter->countryIds())) {
            Arr::set($attributes, 'country', $newsletter->countryIds());
        }

        return $attributes;
    }

    public function processNewsletter(Newsletter $newsletter): bool
    {
        policy_authorize(NewsletterPolicy::class, 'process', $newsletter);

        $newsletter->updateQuietly(['status' => Newsletter::PENDING_STATUS]);

        return true;
    }

    public function reprocessNewsletter(Newsletter $newsletter): bool
    {
        $this->handleStatus($newsletter);

        if ($this->handleArchive($newsletter)) {
            return true;
        }

        policy_authorize(NewsletterPolicy::class, 'reprocess', $newsletter);

        $newsletter->updateQuietly(['status' => Newsletter::PENDING_STATUS]);

        return true;
    }

    public function stopNewsletter(Newsletter $newsletter): bool
    {
        policy_authorize(NewsletterPolicy::class, 'stop', $newsletter);

        $newsletter->updateQuietly([
            'status' => Newsletter::STOPPED_STATUS,
        ]);

        return true;
    }

    public function resendNewsletter(Newsletter $newsletter): bool
    {
        policy_authorize(NewsletterPolicy::class, 'resend', $newsletter);

        $newsletter->updateQuietly([
            'status'       => Newsletter::PENDING_STATUS,
            'total_sent'   => 0,
            'last_sent_id' => 0,
        ]);

        return true;
    }

    public function shouldSend(?Newsletter $newsletter): bool
    {
        if (!$newsletter instanceof Newsletter) {
            return false;
        }

        return $newsletter->status === Newsletter::SENDING_STATUS;
    }

    public function sliceUserIds(Newsletter $newsletter, array $userIds): array
    {
        $round = $newsletter->round;

        return array_slice($userIds, 0, $round);
    }

    public function updateTotalUser(Newsletter $newsletter): void
    {
        $total = $this->buildQueryForNewsletter($newsletter)->count();

        $newsletter->updateQuietly(['total_users' => $total]);
    }

    public function handleSendNewsletter(Newsletter $newsletter, array $userIds): void
    {
        $newsletter->refresh();

        if (!$this->shouldSend($newsletter)) {
            return;
        }

        $users = UserModel::query()->whereIn('id', $userIds)
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            Notification::send($user, new NewsletterNotification($newsletter));

            $newsletter->updateQuietly([
                'last_sent_id' => $user->entityId(),
                'total_sent'   => $newsletter->total_sent + 1,
            ]);

            $newsletter->refresh();
        }
    }

    public function handleStatus(Newsletter $newsletter): void
    {
        if (
            $newsletter->total_users == $newsletter->total_sent
            && $newsletter->status === Newsletter::SENDING_STATUS
        ) {
            $newsletter->updateQuietly(['status' => Newsletter::COMPLETED_STATUS]);
        }
    }

    public function handleArchive(Newsletter $newsletter): bool
    {
        if ($newsletter->status !== Newsletter::COMPLETED_STATUS) {
            return false;
        }

        if ($newsletter->archive) {
            return false;
        }

        $newsletter->delete();

        return true;
    }

    private function userAdminRepository(): UserAdminRepositoryInterface
    {
        return resolve(UserAdminRepositoryInterface::class);
    }

    public function sendTest(array $recipients, int $newsletterId): Newsletter
    {
        $newsletter     = $this->find($newsletterId);
        $newsletterText = $newsletter?->newsletterText;

        $content = [
            'subject' => $newsletter?->subject,
            'line'    => $newsletterText?->text_html,
        ];

        foreach ($recipients as $recipient) {
            Arr::set($content, 'variables', [
                'recipient_full_name'  => $recipient,
                'recipient_first_name' => $recipient,
                'recipient_last_name'  => $recipient,
            ]);

            Mail::to($recipient)->send(new Mailable($content));
        }

        return $newsletter;
    }
}
