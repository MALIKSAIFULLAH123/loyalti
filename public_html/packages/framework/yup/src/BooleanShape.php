<?php

namespace MetaFox\Yup;

/**
 * @link     https://dev-docs.metafoxapp.com/frontend/validation#boolean
 * @category framework
 */
class BooleanShape extends MixedShape
{
    public function __construct()
    {
        $this->setAttribute('type', 'boolean');
    }
}
