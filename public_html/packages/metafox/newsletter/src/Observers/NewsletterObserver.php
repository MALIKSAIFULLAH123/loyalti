<?php

namespace MetaFox\Newsletter\Observers;

use MetaFox\Newsletter\Models\Newsletter;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class NewsletterObserver.
 */
class NewsletterObserver
{
    public function created(Newsletter $newsletter): void
    {
    }

    public function updated(Newsletter $newsletter): void
    {
        $this->newsletterAdminRepository()->handleStatus($newsletter);
        $this->newsletterAdminRepository()->handleArchive($newsletter);
    }

    public function forceDeleted(Newsletter $newsletter): void
    {
        $newsletter->newsletterText()->delete();

        $newsletter->roles()->sync([]);
        $newsletter->genders()->sync([]);
        $newsletter->countries()->sync([]);
    }

    private function newsletterAdminRepository(): NewsletterAdminRepositoryInterface
    {
        return resolve(NewsletterAdminRepositoryInterface::class);
    }
}
