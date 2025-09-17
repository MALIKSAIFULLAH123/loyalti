<?php

namespace MetaFox\User\Http\Resources\v1\ExportProcess\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Http\Requests\v1\User\IndexRequest;
use MetaFox\User\Models\ExportProcess as Model;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\User as UserSupport;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class ExportUserForm
 *
 * @property ?Model $resource
 * @ignore
 */
class ExportUserForm extends AbstractForm
{
    protected array $params = [];
    protected const SECTION_BASIC_INFO   = 'basic_info';
    protected const SECTION_MORE_INFO    = 'more_info';
    protected const SECTION_LOCALIZATION = 'localization';
    protected const SECTION_CUSTOM_FIELD = 'custom_field';
    protected const SECTIONS             = [
        self::SECTION_BASIC_INFO   => 'user::phrase.basic_information',
        self::SECTION_LOCALIZATION => 'core::phrase.localization',
        self::SECTION_MORE_INFO    => 'user::phrase.more_information',
        self::SECTION_CUSTOM_FIELD => 'user::phrase.custom_fields',
    ];

    protected const SECTION_COLLAPSIBLE = [
        self::SECTION_MORE_INFO,
        self::SECTION_CUSTOM_FIELD,
    ];

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.selected_property_export_user'))
            ->action(apiUrl('admin.user.export-process.store'))
            ->asPost()
            ->setValue([
                'filters' => $this->params,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::alert('properties')
                ->message(__p('user::phrase.selected_property_export_user'))
                ->asInfo(),
        );

        foreach (self::SECTIONS as $name => $label) {
            $options = $this->mappingPropertyOptions($name);

            if (empty($options)) {
                continue;
            }

            $isCollapsible = in_array($name, self::SECTION_COLLAPSIBLE);
            $section       = $this->addSection(['name' => $name])
                ->label(__p($label))
                ->collapsed($isCollapsible)
                ->collapsible($isCollapsible);

            $section->addFields(
                Builder::checkboxGroup('properties.' . $name)
                    ->enableCheckAll()
                    ->required()
                    ->options($options)
                    ->yup(Yup::array()),
            );
        }

        $this->addDefaultFooter();
    }

    protected function mappingPropertyOptions(string $sectionName): array
    {
        $properties = match ($sectionName) {
            self::SECTION_CUSTOM_FIELD => UserFacade::getPropertiesCustomField(user()),
            self::SECTION_BASIC_INFO   => UserSupport::getPropertiesBasicInfo(),
            self::SECTION_LOCALIZATION => UserSupport::getPropertiesLocalization(),
            self::SECTION_MORE_INFO    => UserSupport::getPropertiesMoreInfo(),
        };

        $results = [];

        Arr::mapWithKeys($properties, function ($label, $value) use (&$results) {
            return $results[] = [
                'value' => $value,
                'label' => $label,
            ];
        });
        return $results;
    }

    public function boot(IndexRequest $request)
    {
        if (request()->has('ids')) {
            $this->params = ['ids' => json_decode($request->get('ids'), true)];
            return;
        }

        $this->params = $request->validated();
    }
}
