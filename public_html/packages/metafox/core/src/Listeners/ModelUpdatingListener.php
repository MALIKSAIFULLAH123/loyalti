<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Core\Listeners\Traits\HandleResourceTextTrait;
use MetaFox\Core\Traits\IsPrivacyItemTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\IsPrivacyItemInterface;
use MetaFox\Platform\Contracts\ResourceText;

class ModelUpdatingListener
{
    use HandleResourceTextTrait;
    use IsPrivacyItemTrait;

    /**
     * @param Model $model
     *
     * @return void
     */
    public function handle(Model $model): void
    {
        if ($model instanceof Content) {
            $this->handleFeaturedAt($model);
        }

        if ($model instanceof ResourceText) {
            if ($model->isDirty(['text'])) {
                $this->handleResourceText($model);
            }

            $this->handleUpdateHashtag($model);
        }

        //delete all privacy member before update new value
        if ($model instanceof IsPrivacyItemInterface) {
            $className = get_class($model);
            $oldModel  = new $className($model->getRawOriginal());
            $this->handlePrivacyItemForDeleted($oldModel);
        }
    }

    private function handleFeaturedAt(Content $model): void
    {
        if ($model instanceof HasFeature) {
            if (HasFeature::IS_FEATURED == $model->is_featured) {
                $model->featured_at = Carbon::now();
            }
        }
    }

    private function handleUpdateHashtag(ResourceText $model): bool
    {
        $resource = $model->resource;
        if (!$resource instanceof HasHashTag) {
            return false;
        }

        $newHashtags = implode(',', parse_output()->getHashtags($model->text_parsed));
        if (empty($newHashtags)) {
            $resource->tagData()->sync([]);
        }

        app('events')->dispatch('hashtag.create_hashtag', [$resource->user, $resource, $model->text_parsed], true);

        return true;
    }
}
