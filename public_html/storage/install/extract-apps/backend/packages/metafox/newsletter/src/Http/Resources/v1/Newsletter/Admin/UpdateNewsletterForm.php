<?php

namespace MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Newsletter\Models\Newsletter as Model;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateNewsletterForm.
 *
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateNewsletterForm extends CreateNewsletterForm
{
    protected NewsletterAdminRepositoryInterface $repository;

    public function boot(NewsletterAdminRepositoryInterface $repository, Request $request, ?int $id = null): void
    {
        $this->viewOnly   = $request->get('viewOnly', $this->viewOnly);
        $this->repository = $repository;
        $this->resource   = $this->repository->find($id);
    }

    protected function prepare(): void
    {
        $newsletterText = $this->resource->newsletterText;
        $title          = $this->viewOnly ? __p('newsletter::phrase.view_newsletter') : __p('newsletter::phrase.edit_newsletter');

        $this->title($title)
            ->action(url_utility()->makeApiUrl('admincp/newsletter/newsletter/' . $this->resource->id))
            ->asPut()
            ->setValue([
                'archive'          => $this->resource->archive,
                'override_privacy' => $this->resource->override_privacy,
                'roles'            => $this->resource->rolesIds(),
                'countries'        => $this->resource->countryIds(),
                'genders'          => $this->resource->genderIds(),
                'age_from'         => $this->resource->age_from,
                'age_to'           => $this->resource->age_to,
                'round'            => $this->resource->round,
                'subject'          => Language::getPhraseValues($this->resource->subject_raw),
                'text'             => $newsletterText?->text_raw ? Language::getPhraseValues($newsletterText?->text_raw) : null,
                'text_html'        => $newsletterText?->text_html_raw ? Language::getPhraseValues($newsletterText?->text_html_raw) : null,
                'channel_mail'     => Arr::get($this->resource->channels, 'mail', 0),
                'channel_sms'      => Arr::get($this->resource->channels, 'sms', 0),
            ]);
    }
}
