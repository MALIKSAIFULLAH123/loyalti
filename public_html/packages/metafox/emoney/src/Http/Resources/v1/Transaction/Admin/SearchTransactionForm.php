<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction\Admin;

use MetaFox\EMoney\Models\Transaction as Model;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchTransactionForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchTransactionForm extends \MetaFox\EMoney\Http\Resources\v1\Transaction\SearchTransactionForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/emoney/transaction')
            ->acceptPageParams(['from_date', 'to_date', 'status', 'buyer', 'seller', 'base_currency', 'source', 'type'])
            ->submitAction('@formAdmin/search/SUBMIT')
            ->setValue([
                'from_date' => null,
                'to_date'   => null,
            ]);
    }

    protected function getUserField(): ?AbstractField
    {
        $field = parent::getUserField();

        return $field->label(__p('ewallet::admin.sender'))
            ->placeholder(__p('ewallet::admin.sender'));
    }

    protected function getOwnerField(): ?AbstractField
    {
        return Builder::text('seller')
            ->forAdminSearchForm()
            ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
            ->placeholder(__p('ewallet::admin.receiver'))
            ->sxFieldWrapper($this->getResponsiveSx())
            ->label(__p('ewallet::admin.receiver'));
    }
}
