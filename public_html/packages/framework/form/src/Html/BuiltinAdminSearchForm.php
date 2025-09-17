<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * Class BuiltinAdminSearchForm.
 *
 * Generic search form class for admincp.
 * @driverName ignore
 */
class BuiltinAdminSearchForm extends AbstractForm
{
    protected function initialize(): void
    {
        $this->acceptPageParams(['q']);

        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }
}
