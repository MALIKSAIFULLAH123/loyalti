<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Foxexpert\Sevent\Http\Requests\v1\Sevent\CreateFormRequest;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateSeventForm.
 */
class UpdateSeventMobileForm extends StoreSeventMobileForm
{
    public function boot(CreateFormRequest $request, SeventRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        $this->setOwner($this->resource->owner);
        policy_authorize(SeventPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $seventText = $this->resource->seventText;
        $privacy  = $this->resource->privacy;

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $this->title(__p('sevent::phrase.edit_sevent'))
            ->action("sevent/{$this->resource->entityId()}")
            ->asPut()
            ->setValue([
                'title'       => $this->resource->title,
                'module_id'   => $this->resource->module_id,
                'owner_id'    => $this->resource->owner_id,
                'text'        => $seventText != null ? parse_output()->parse($seventText->text_parsed) : '',
                'categories'  => $this->resource->categories->pluck('id')->toArray(),
                'privacy'     => $privacy,
                'published'   => !$this->resource->is_draft,
                'tags'        => $this->resource->tags,
                'attachments' => $this->resource->attachmentsForForm(),
                'draft'       => 0,
            ]);
    }
}
