<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction\Admin;

use MetaFox\Core\Support\Facades\Language;
use MetaFox\Like\Models\Reaction as Model;
use MetaFox\Like\Repositories\ReactionAdminRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditReactionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditReactionForm extends CreateReactionForm
{
    public function boot(ReactionAdminRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->action(apiUrl('admin.like.reaction.update', ['reaction' => $this->resource->entityId()]))
            ->asPut()
            ->setValue([
                'title'     => Language::getPhraseValues($this->resource->title_var),
                'is_active' => $this->resource->is_active,
                'color'     => "#{$this->resource->color}",
                'icon_font' => $this->resource->icon_font,
                'image'     => [
                    'id' => $this->resource->icon_file_id,
                ],
            ]);
    }
}
