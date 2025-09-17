<?php

namespace MetaFox\SEO;

class SeoMetaData extends \ArrayObject
{
    public function addBreadcrumb($label, $to = null)
    {
        if (!$label) {
            return;
        }

        $breadcrumbs = $this->offsetGet('breadcrumbs');

        if (!$breadcrumbs) {
            $breadcrumbs = [];
        }
        $breadcrumbs[] = ['label' => $label, 'to' => $to];

        $this->offsetSet('breadcrumbs', $breadcrumbs);
    }
}
