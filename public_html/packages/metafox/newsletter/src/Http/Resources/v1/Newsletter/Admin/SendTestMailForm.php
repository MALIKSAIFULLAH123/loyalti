<?php

namespace MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin;

use MetaFox\Form\Builder as Builder;
use MetaFox\Newsletter\Models\Newsletter as Model;
use MetaFox\Newsletter\Repositories\NewsletterAdminRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SendTestMailForm.
 *
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SendTestMailForm extends CreateNewsletterForm
{
    protected NewsletterAdminRepositoryInterface $repository;

    public function boot(NewsletterAdminRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $this->repository->find($id);
    }

    protected function prepare(): void
    {
        $context = user();
        $this->title(__p('newsletter::phrase.send_test_mail'))
            ->action(url_utility()->makeApiUrl('admincp/newsletter/test/' . $this->resource->id))
            ->asPatch()
            ->setValue([
                'recipients' => [$context->email],
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::tags('recipients')
                    ->required()
                    ->disableSuggestion()
                    ->label(__p('newsletter::phrase.sent_emails'))
                    ->description(__p('newsletter::phrase.separate_multiple_emails_with_commas_or_enter'))
                    ->yup(
                        Yup::array()
                            ->required()
                            ->of(Yup::string()->email(__p('validation.invalid_email_address')))
                    ),
            );

        $this->addDefaultFooter();
    }

}
