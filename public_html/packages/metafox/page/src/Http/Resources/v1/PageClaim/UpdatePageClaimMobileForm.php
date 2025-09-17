<?php

namespace MetaFox\Page\Http\Resources\v1\PageClaim;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Page\Models\PageClaim as Model;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdatePageClaimMobileForm.
 * @property Model $resource
 */
class UpdatePageClaimMobileForm extends AbstractForm
{
    protected const MAX_LENGTH_MESSAGE = 500;

    public function boot(PageClaimRepositoryInterface $claimRepository, ?int $id): void
    {
        $this->resource  = $claimRepository->find($id);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $resource = $this->resource;

        if (!$resource instanceof Model) {
            throw new AuthenticationException();
        }

        $this->asPut()
            ->title(__p('page::phrase.edit_claim_page'))
            ->action(url_utility()->makeApiUrl('page-claim/' . $resource->entityId()))
            ->setValue([
                'message' => $resource?->message,
            ]);
    }

    protected function initialize(): void
    {
        $this->addHeader(['showRightHeader' => true])->component('FormHeader');
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::textArea('message')
                ->maxLength(self::MAX_LENGTH_MESSAGE)
                ->description(__p('page::phrase.claim_message_description'))
                ->label(__p('core::phrase.message'))
                ->yup(
                    Yup::string()
                        ->maxLength(self::MAX_LENGTH_MESSAGE)
                ),
        );
    }
}
