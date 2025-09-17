<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class TagsField.
 */
class TagsField extends AbstractField
{
    public function initialize(): void
    {
        $this->component(MetaFoxForm::FIELD_TAG)
            ->maxLength(255)
            ->name('tags')
            ->fullWidth(true)
            ->marginNormal()
            ->variant('outlined')
            ->searchUrl('/hashtag/suggestion')
            ->description(__p('core::phrase.separate_multiple_topics_with_enter'));
    }

    public function searchUrl(string $url): self
    {
        return $this->setAttribute('search_endpoint', $url);
    }

    public function disableSuggestion(): self
    {
        return $this->setAttribute('disableSuggestion', true);
    }
    public function allowSpaceNewTag(): self
    {
        return $this->setAttribute('allowSpaceNewTag', true);
    }
}
