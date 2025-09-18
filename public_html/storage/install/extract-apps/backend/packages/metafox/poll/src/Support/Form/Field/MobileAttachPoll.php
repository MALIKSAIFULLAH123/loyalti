<?php
namespace MetaFox\Poll\Support\Form\Field;

use MetaFox\Form\AbstractField;

class MobileAttachPoll extends AbstractField
{
    public const COMPONENT_NAME = 'AttachPoll';

    public function initialize(): void
    {
        $this->setAttributes([
            'component'     => self::COMPONENT_NAME,
            'name'          => 'integrated_item',
            'formUrl'      => url_utility()->makeApiUrl('core/mobile/form/poll.integration_poll_mobile'),
            'fullWidth'     => true,
            'variant'       => 'outlined',
        ]);
    }
}
