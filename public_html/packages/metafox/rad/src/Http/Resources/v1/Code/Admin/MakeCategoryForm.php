<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

use Illuminate\Support\Facades\DB;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * Class MakeCategoryForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeCategoryForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Model')
            ->action('admincp/rad/code/make/category')
            ->asPost()
            ->setValue([
                'package'          => 'metafox/core',
                '--entity'         => '',
                '--table'          => '',
                '--overwrite'      => false,
                '--dry'            => false,
                '--ver'            => 'v1',
                '--has-repository' => true,
                '--has-factory'    => true,
                '--test'           => false,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::selectPackage('package')
                ->label(__p('core::phrase.package_name'))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->nullable()
                ),
            Builder::text('name')
                ->label('Model Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('entity')
                ->label('Entity Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::choice('table')
                ->label('Schema Name')
                ->required()
                ->options($this->getSchemaOptions())
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::hidden('ver'),
            Builder::hidden('hasRepository'),
            Builder::hidden('--has-factory'),
            Builder::checkbox('--dry')
                ->label('Dry run - does not write to files?')
                ->asBoolean(),
            Builder::checkbox('overwrite')
                ->label('Overwrite existing files?')
                ->asBoolean(),
            Builder::checkbox('test')
                ->label('Generate sample UnitTest?')
                ->asBoolean(),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label('Generate Code'),
                Builder::cancelButton(),
            );
    }

    /**
     * @return array<int, mixed>
     * @throws \Doctrine\DBAL\Exception
     * @ignore
     * @codeCoverageIgnore
     */
    private function getSchemaOptions(): array
    {
        $tableNames = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $results    = [];

        foreach ($tableNames as $tableName) {
            if (strpos($tableName, '.') !== false) {
                continue;
            }

            $results[] = ['label' => $tableName, 'value' => $tableName];
        }

        return $results;
    }
}
