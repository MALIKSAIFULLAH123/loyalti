<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewAdminScope;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchGroupForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchGroupForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('group/group/browse')
            ->acceptPageParams(['q', 'user_name', 'privacy_type', 'view', 'category_id', 'created_from', 'created_to'])
            ->submitAction(MetaFoxForm::FORM_SUBMIT_ACTION_SEARCH)
            ->setValue([
                'view'         => ViewAdminScope::VIEW_DEFAULT,
                'view_more'    => 1,
                'created_from' => null,
                'created_to'   => null,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal()->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('core::phrase.title'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::text('user_name')
                ->label(__p('core::web.created_by_'))
                ->placeholder(__p('core::web.created_by_'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense(),
            Builder::choice('privacy_type')
                ->label(__p('group::phrase.group_privacy'))
                ->placeholder(__p('group::phrase.group_privacy'))
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense()
                ->options($this->getRegOptions()),
            Builder::choice('view')
                ->fullWidth()
                ->sizeSmall()
                ->sxFieldWrapper(['maxWidth' => 220])
                ->marginDense()
                ->label(__p('core::phrase.view'))
                ->options(ViewAdminScope::getViewOptions()),
            Builder::category('category_id')
                ->sxFieldWrapper(['maxWidth' => 220])
                ->sizeSmall()
                ->multiple(false)
                ->marginDense()
                ->setAttribute('options', $this->getCategoryOptions()),
            Builder::date('created_from')
                ->label(__p('core::phrase.created_from'))
                ->startOfDay()
                ->forAdminSearchForm()
                ->yup(Yup::date()->nullable()
                    ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_from')]))),
            Builder::date('created_to')
                ->label(__p('core::phrase.created_to'))
                ->endOfDay()
                ->forAdminSearchForm()
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'created_from'])
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('core::phrase.created_to')]))
                        ->setError('min', __p('validation.the_end_time_should_be_greater_than_the_start_time', [
                            'end_time'   => __p('core::phrase.created_to'),
                            'start_time' => __p('core::phrase.created_from'),
                        ]))
                ),
        );

        CustomFieldFacade::loadFieldSearch($basic, [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'resolution'   => MetaFoxConstant::RESOLUTION_ADMIN,
        ]);

        $basic->addFields(
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->sizeMedium(),
        );
    }

    protected function getCategoryOptions(): array
    {
        /**@var $categoryRepository CategoryRepositoryInterface */
        $categoryRepository = resolve(CategoryRepositoryInterface::class);
        $collections        = $categoryRepository->getCategories(false);

        if ($collections->isEmpty()) {
            return [];
        }

        return $collections->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    protected function getRegOptions(): array
    {
        return [
            [
                'value' => PrivacyTypeHandler::PUBLIC,
                'label' => __p('group::phrase.public'),
            ],
            [
                'value' => PrivacyTypeHandler::CLOSED,
                'label' => __p('group::phrase.closed'),
            ],
            [
                'value' => PrivacyTypeHandler::SECRET,
                'label' => __p('group::phrase.secret'),
            ],
        ];
    }
}
