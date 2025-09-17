<?php

namespace MetaFox\QuotaControl\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Authorization\Http\Resources\v1\Permission\Admin\EditPermissionForm as Form;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\Yup\Yup;

class EditPermissionForm extends Form
{
    /**
     * booted.
     *
     * @return void
     */
    protected function booted()
    {
        $permissionRepository = resolve(PermissionRepositoryInterface::class);

        $this->permissions = $permissionRepository->getPermissionsForEdit(user(), [
            'actions' => ['quota_control'],
        ]);
    }

    /**
     * @param string|null $moduleName
     *
     * @return void
     */
    protected function buildBasicSection(?string $moduleName): void
    {
        $values = [
            'module_id' => $moduleName,
        ];

        $this->addBasic()
            ->addField(
                Builder::typography('_alert_info')
                    ->plainText(__p('quota::phrase.quota_control_permission_info_message'))
            );

        foreach ($this->permissions as $row) {
            // Getting value per permission
            $name  = $row->name;
            $value = $row->data_type == MetaFoxDataType::INTEGER ? (int) $this->role->getPermissionValue($name) : 0;

            Arr::set($values, $name, $value);

            $section = $this->addSection(['name' => 'entity_' . $row->entity_type])
                ->label(__p(sprintf('%s::phrase.quota_control_%s', $row->module_id, $row->entity_type)))
                ->collapsed()
                ->collapsible();

            $section->addFields(
                Builder::text($name)
                    ->preventScrolling()
                    ->required()
                    ->label(__p($this->getPhrase($row, 'label')))
                    ->marginNormal()
                    ->asNumber()
                    ->yup(
                        Yup::number()
                            ->required()
                            ->int()
                            ->min(0)
                            ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
                    ),
            );
        }

        $this->setValue($values);
    }

    protected function getPhrase($row, string $type): string
    {
        return Str::snake(sprintf(
            '%s::permission.can_%s_%s_%s',
            $row->module_id,
            Str::after($row->name, '.'),
            $row->entity_type,
            $type
        ));
    }
}
