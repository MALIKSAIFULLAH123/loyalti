<?php

namespace MetaFox\Core\Support\Link\Providers;

use MetaFox\Core\Contracts\LinkSupportContract;

/**
 * @SuppressWarnings(PHPMD)
 */
abstract class AbstractLinkProvider implements LinkSupportContract
{
    /**
     * @param array<mixed> $options
     */
    public function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options): void
    {
    }
}
