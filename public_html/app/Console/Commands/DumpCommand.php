<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Repositories\Eloquent\PermissionRepository;
use MetaFox\Authorization\Repositories\PermissionSettingRepositoryInterface;
use MetaFox\Core\Models\AdminSearch;
use MetaFox\Core\Repositories\AdminSearchRepositoryInterface;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\MetaFoxDataType;
use MetaFox\SEO\Models\Meta;
use MetaFox\User\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Input\InputOption;

class DumpCommand extends Command
{
    protected ?Spreadsheet $spreadsheet = null;

    protected array $urlFields = ['Id', 'App', 'Url', 'Title', 'Resolution', 'Skip'];

    protected array $userFields = ['Id', 'Name', 'Username', 'Email', 'Password', 'Url', 'Skip'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'metafox:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump site urls';
    private Worksheet $worksheet;
    private int $rowIndex = 0;
    private array $fields;
    private int $sheetIndex = 0;

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function handle(): int
    {
        $this->spreadsheet = new Spreadsheet();

        $this->dumpApps();
        $this->dumpPageNames();
        $this->dumpPermissions();
        //$this->dumpUrls();

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('metafox.xlsx');

        if (is_dir('../wdio')) {
            copy('metafox.xlsx', '../wdio/src/fixtures/metafox.xlsx');
        }

        $this->comment('Updated '.'metafox.xlsx');

        return 0;
    }

    protected function dumpPageNames()
    {
        /** @var Meta[] $rows */
        $rows = Meta::query()->orderBy('module_id')->get();
        $this->addWorkSheet('PageNames',
            ['ID', 'Page Name', 'App', 'Resolution', 'Url', 'Meta ID', 'Title', 'Description']);

        $matrix = [];
        foreach ($rows as $row) {
            $this->addRowToCurrentWorksheet([
                'ID'           => '',
                'Resolution'   => $row->resolution,
                'Meta ID'      => $row->name,
                'App'          => $row->module_id,
                'Url'          => $row->url,
                'Title'        => __p($row->phrase_title),
                'Desccription' => __p($row->phrase_description)
            ]);
        }
    }

    protected function dumpApps()
    {
        $this->addWorkSheet('Apps', ['ID', 'Name', 'Title', 'Type', 'Is Core', 'Version', 'Author', 'Skip']);

        /** @var Package[] $apckage */
        $rows = resolve(PackageRepositoryInterface::class)->getModel()->newQuery()->orderBy('name')->get();

        foreach ($rows as $row) {
            $this->addRowToCurrentWorksheet([
                'Name'    => $row->alias,
                'Type'    => $row->type,
                'Is Core' => $row->is_core ? 'yes' : 'no',
                'Version' => $row->version,
                'Author'  => $row->author,
                'Title'   => $row->title,
                'Skip'    => ''
            ]);
        }
    }

    protected function dumpPermissions()
    {
        $fields = ['Id', 'Skip', 'App', 'App Name', 'Page Name', 'Data Type', 'Test ID', 'Label'];
        $context = User::find(1);
        $repo = resolve(PermissionRepository::class);

        /** @var Role[] $roles */
        $roles = Role::all();
        foreach ($roles as $role) {
            $fields[] = $role->name;
        }

        // app field title
        $this->addWorkSheet('ACP-UserPermissions', $fields);
        $excludedActions = resolve(PermissionSettingRepositoryInterface::class)->getExcludedActions();
        $matrix = [];
        foreach ($roles as $role) {
            /** @var Permission[] $rows */
            $rows = $repo->getPermissionsForEdit($context, [
                'exclude_actions' => $excludedActions,
                'role'            => $role->id,
                'limit'           => '1000',
                'sort_type'       => ''
            ]);
            foreach ($rows as $row) {
                $testId = Str::camel("field ".str_replace(".", " ", $row->name));
                if (!array_key_exists($row->name, $matrix)) {
                    $matrix[$row->name] = [
                        'Label'     => __p($row->getLabelPhrase()),
                        'Page Name' => "admincp - $row->module_id permissions",
                        'App'       => $row->module_id,
                        'App Name'  => __p("$row->module_id::phrase.app_name"),
                        'Test ID'   => $testId,
                        'Data Type' => $row->data_type,
                    ];
                }
                $value = match ($row->data_type) {
                    MetaFoxDataType::BOOLEAN => $role->hasPermissionTo($row->name) ? "yes" : "no",
                    MetaFoxDataType::INTEGER => (int) $role->getPermissionValue($row->name),
                    default => "",
                };
                $matrix[$row->name][$role->name] = $value;
            }
        }

        foreach ($matrix as $item) {
            $this->addRowToCurrentWorksheet($item);
        }
    }

    protected function dumpUrls()
    {
        $this->addWorkSheet('Urls', $this->urlFields);
        $this->dumpMenuUrls();
        $this->dumpAdminSearch();
    }

    protected function addWorkSheet(string $title, array $fields)
    {
        $this->worksheet = new Worksheet($this->spreadsheet, $title);
        $this->worksheet->setTitle($title);
        $this->spreadsheet->addSheet($this->worksheet, $this->sheetIndex);
        $this->sheetIndex = $this->sheetIndex + 1;

        $this->fields = $fields;
        $this->rowIndex = 1;

        foreach ($this->fields as $index => $field) {
            $this->worksheet->setCellValueByColumnAndRow($index + 1, 1, $field);
        }
    }

    protected function addRowToCurrentWorksheet(array $data): void
    {
        $this->rowIndex = $this->rowIndex + 1;
        foreach ($this->fields as $index => $name) {
            $this->worksheet->setCellValueByColumnAndRow($index + 1, $this->rowIndex, $data[$name] ?? null);
        }
    }

    public function dumpMenuUrls(): void
    {
        $exists = [];

        /** @var \MetaFox\Menu\Models\MenuItem[] $query */
        $query = resolve(MenuItemRepositoryInterface::class)
            ->getModel()
            ->newQuery()
            ->whereNull('as')
            ->whereNull('value')
            ->whereNotNull('to')
            ->whereNot([
                ['resolution', '=', 'mobile'],
            ])
            ->orderBy('resolution')
            ->orderBy('to')
            ->cursor();

        foreach ($query as $item) {
            if (isset($exists[$item->to])) {
                continue;
            }
            $exists[$item->to] = true;

            $this->addRowToCurrentWorksheet([
                'Id'         => $item->id,
                'Name'       => $item->testid || $item->name,
                'Url'        => $item->to,
                'App'        => $item->module_id,
                //'App Name'   => __p($item->module_id.'::phrase.app_name'),
                'Title'      => __p($item->label),
                'Resolution' => $item->resolution,
                'Skip'       => $item->is_active ? '' : 'yes',
            ]);
        }
    }

    public function dumpAdminSearch(): void
    {
        $exists = [];

        /** @var AdminSearch[] $searchs */
        $searchs = resolve(AdminSearchRepositoryInterface::class)
            ->getModel()->newQuery()
            ->orderBy('url')
            ->cursor();

        foreach ($searchs as $item) {
            if (isset($exists[$item->url])) {
                continue;
            }
            $exists[$item->url] = true;

            $this->addRowToCurrentWorksheet([
                'Id'         => $item->id,
                'Name'       => $item->title,
                'Url'        => $item->url,
                'App'        => $item->module_id,
                //'App Name'   => __p($item->module_id.'::phrase.app_name'),
                'Title'      => $item->title,
                'Resolution' => 'admin',
            ]);
        }

        $this->info(sprintf("dump %d admin_search_urls", $this->rowIndex - 2));
    }

    protected function getOptions()
    {
        return [
            ['urls', null, InputOption::VALUE_NONE],
            ['lang', null, InputOption::VALUE_NONE],
        ];
    }
}
