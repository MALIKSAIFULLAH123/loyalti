<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Localize\Http\Resources\v1\Phrase\Admin;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * Phrase Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class PhraseWebSetting.
 */
class WebSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('admincp/phrase');

        $this->add('deleteItem')
            ->apiUrl('admincp/phrase/:id');

        $this->add('deleteItems')
            ->apiUrl('admincp/phrase')
            ->asDelete();

        $this->add('editItem')
            ->apiUrl('admincp/phrase/form/:id');

        $this->add('addItem')
            ->apiUrl('admincp/phrase/form');
    }
}
