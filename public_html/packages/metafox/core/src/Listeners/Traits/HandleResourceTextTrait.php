<?php

namespace MetaFox\Core\Listeners\Traits;

use MetaFox\Platform\Contracts\ResourceText;

trait HandleResourceTextTrait
{
    public function handleResourceText(ResourceText $model): void
    {
        $model->text_parsed = parse_input()->prepare($model->text);
        $model->text = parse_input()->clean($model->text, false, true);
    }
}
