<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractForm;
use MetaFox\Forum\Models\Forum as Model;
use MetaFox\Forum\Policies\ForumPolicy;
use MetaFox\Forum\Repositories\ForumAdminRepositoryInterface;
use MetaFox\Forum\Repositories\ForumRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditForumForm.
 * @property ?Model $resource
 */
class EditForumForm extends CreateForumForm
{
    protected function prepare(): void
    {
        $this->title(__p('forum::phrase.edit_forum'))
            ->asPut()
            ->action('admincp/forum/forum/' . $this->resource->entityId())
            ->setValue([
                'title'       => Language::getPhraseValues($this->resource->title_var),
                'parent_id'   => $this->resource->parent_id,
                'is_closed'   => (int) $this->resource->is_closed,
                'description' => Language::getPhraseValues($this->resource->description_var),
            ]);
    }

    public function boot(?int $id = null)
    {
        $context = user();

        $this->resource = resolve(ForumRepositoryInterface::class)->find($id);

        policy_authorize(ForumPolicy::class, 'update', $context, $this->resource);
    }

    protected function isEdit(): bool
    {
        return true;
    }

    protected function getParentOptions(): array
    {
        return resolve(ForumAdminRepositoryInterface::class)->getUpdateForumsForForm($this->resource);
    }
}
