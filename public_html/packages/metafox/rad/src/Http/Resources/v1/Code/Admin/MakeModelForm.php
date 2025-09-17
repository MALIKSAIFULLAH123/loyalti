<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Rad\Http\Resources\v1\Code\Admin;

use Illuminate\Support\Facades\DB;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Html\Hidden;
use MetaFox\Yup\Yup;

/**
 * Class MakeModelForm.
 * @ignore
 * @codeCoverageIgnore
 */
class MakeModelForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this
            ->title('Generate Model')
            ->action('admincp/rad/code/make/model')
            ->asPost()
            ->setValue(
                [
                    'package'          => 'metafox/core',
                    '--entity'         => '',
                    '--dry'            => false,
                    '--table'          => '',
                    '--content'        => false,
                    '--overwrite'      => false,
                    '--ver'            => 'v1',
                    '--has-text'       => false,
                    '--has-tag'        => false,
                    '--has-policy'     => false,
                    '--has-privacy'    => false,
                    '--has-repository' => true,
                    '--has-factory'    => false,
                    '--has-category'   => false,
                    '--has-observer'   => false,
                    '--test'           => false,
                ]
            );
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
                        ->nullable(false)
                ),
            Builder::text('--name')
                ->label('Model Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('--entity')
                ->label('Entity Name')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::choice('--table')
                ->label('Schema Name')
                ->required()
                ->options($this->getSchemaOptions())
                ->yup(
                    Yup::string()
                        ->required()
                ),
            new Hidden(['name' => '--ver']),
            Builder::checkbox('--has-repository')
                ->label('Has Repository?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--has-factory')
                ->label('Has Model Factory?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--has-policy')
                ->label('Has Authorization?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--has-text')
                ->label('Has Text Data?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--has-category')
                ->label('Has Category Data?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--has-tag')
                ->label('Has Tags Data ?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--has-privacy')
                ->label('Has Activity Feed')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--has-observer')
                ->label('Has Model Observer ?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--overwrite')
                ->label('Overwrite existing files?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--test')
                ->label('Generate sample unittest?')
                ->checkedValue(true)
                ->uncheckedValue(false),
            Builder::checkbox('--dry')
                ->label('Dry run - does not write to files?')
                ->checkedValue(true)
                ->uncheckedValue(false),
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
