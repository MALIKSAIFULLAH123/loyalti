<?php

namespace MetaFox\Contact\Support;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use MetaFox\Contact\Contracts\Contact as ContractsContact;
use MetaFox\Contact\Repositories\CategoryRepositoryInterface;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

/**
 * Class Contact.
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Contact implements ContractsContact
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
        protected UserRepositoryInterface     $userRepository
    ) {
    }

    public function send(array $params = []): void
    {
        $fullName     = Arr::get($params, 'full_name');
        $subject      = Arr::get($params, 'subject');
        $email        = Arr::get($params, 'email');
        $message      = Arr::get($params, 'message');
        $categoryId   = Arr::get($params, 'category_id', 0);
        $category     = $this->categoryRepository->find($categoryId);
        $recipients   = array_filter(explode(',', Settings::get('contact.staff_emails')));
        $sendResponse = Settings::get('contact.enable_auto_responder', true);

        if (empty($recipients)) {
            throw new Exception('There are no configured recipients for Contact form.');
        }

        $content = [
            'subject' => __p('contact::mail.contact_email_subject', [
                'category' => $category->name,
                'subject'  => $subject,
            ]),
            'line'    => __p('contact::mail.contact_email_html', [
                'full_name' => $fullName,
                'email'     => $email,
                'message'   => $message,
            ]),
        ];

        foreach ($recipients as $recipient) {
            $user = $this->userRepository->findUserByEmail($recipient);

            Arr::set($content, 'variables', [
                'recipient_full_name'  => $user?->full_name ?? $recipient,
                'recipient_first_name' => $user?->first_name ?? $recipient,
                'recipient_last_name'  => $user?->last_name ?? $recipient,
            ]);

            Mail::to($recipient)
                ->send(new Mailable($content));
        }

        if (Arr::get($params, 'send_copy')) {
            Arr::set($content, 'variables', [
                'recipient_full_name'  => $fullName,
                'recipient_first_name' => $fullName,
                'recipient_last_name'  => $fullName,
            ]);

            Mail::to($email)
                ->send(new Mailable($content));
        }

        if ($sendResponse) {
            $this->sendResponse($params);
        }
    }

    public function sendResponse(array $params): void
    {
        $recipient = Arr::get($params, 'email');
        $fullName  = Arr::get($params, 'full_name');

        $content = [
            'subject' => __p('contact::mail.thank_you_for_contacting_us_subject'),
            'line'    => __p('contact::mail.thank_you_for_contacting_us_html'),
        ];

        Arr::set($content, 'variables', [
            'recipient_full_name'  => $fullName,
            'recipient_first_name' => $fullName,
            'recipient_last_name'  => $fullName,
        ]);

        Mail::to($recipient)
            ->send(new Mailable($content));
    }
}
