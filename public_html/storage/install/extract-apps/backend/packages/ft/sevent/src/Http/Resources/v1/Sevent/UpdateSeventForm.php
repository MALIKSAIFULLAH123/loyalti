<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Foxexpert\Sevent\Http\Requests\v1\Sevent\CreateFormRequest;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Html\Submit;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\ResourceGate;
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
class UpdateSeventForm extends StoreSeventForm
{
    public function boot(CreateFormRequest $request, SeventRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        $this->setOwner($this->resource->owner);
        policy_authorize(SeventPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepareAttachedPhotos(array $values): array
    {
        $items = [];

        if ($this->resource->photos->count()) {
            $items = $this->resource->photos->map(function ($photo) {
                return ResourceGate::asItem($photo, null);
            });
        }

        Arr::set($values, 'attached_photos', $items);

        return $values;
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
       
        $values = [
            'title'       => $this->resource->title,
            'short_description'       => $this->resource->short_description,
            'terms'       => $this->resource->terms,
            'online_link'     => $this->resource->online_link,
            'is_online'       => $this->resource->is_online,
            'start_date'       => $this->resource->start_date,
            'end_date'       => $this->resource->end_date,
            'video'       => $this->resource->video,
            'location'      => $this->resource->is_online ? null : $this->resource->toLocationObject(),
            'location_name' => $this->resource->location_name,
            'module_id'   => $this->resource->module_id,
            'owner_id'    => $this->resource->owner_id,
            'course_id'    => $this->resource->course_id,
            'text'        => $seventText != null ? parse_output()->parse($seventText->text_parsed) : '',
            'categories'  => $this->resource->categories->pluck('id')->toArray(),
            'privacy'     => $privacy,
            'published'   => !$this->resource->is_draft,
            'tags'        => $this->resource->tags,
            'attachments' => $this->resource->attachmentsForForm(),
            'draft'       => 0,

            'is_host'     => $this->resource->is_host,
            'host_title'     => $this->resource->host_title,
            'host_contact'     => $this->resource->host_contact,
            'host_website'     => $this->resource->host_website,
            'host_facebook'     => $this->resource->host_facebook,
            'host_description'     => $this->resource->host_description,
        ];

        $values = $this->prepareAttachedPhotos($values);
        $this->title(__p('sevent::phrase.edit_sevent'))
            ->action(url_utility()->makeApiUrl("sevent/{$this->resource->entityId()}"))
            ->setBackProps(__p('core::web.sevent'))
            ->asPut()
            ->setValue($values);
    }

    protected function buildPublishButton(): AbstractField
    {
        return new Submit([
            'label'     => !$this->resource->isDraft() ? __p('core::phrase.update') : __p('core::phrase.publish'),
            'flexWidth' => true,
        ]);
    }

    protected function buildSaveAsDraftButton(): AbstractField
    {
        return new Submit([
            'name'     => 'draft',
            'color'    => 'primary',
            'variant'  => 'outlined',
            'label'    => __p('core::phrase.update'),
            'value'    => 1,
            'showWhen' => ['falsy', 'published'],
        ]);
    }
}
