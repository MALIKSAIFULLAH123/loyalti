<?php

namespace MetaFox\Platform\ApiDoc;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Knuckles\Scribe\Extracting\ParamHelpers;
use Knuckles\Scribe\Tools\DocumentationConfig;
use Knuckles\Scribe\Tools\Utils as u;

class MdxWriter
{
    use ParamHelpers;

    const SPEC_VERSION = '3.0.3';

    private DocumentationConfig $config;

    /**
     * Object to represent empty values, since empty arrays get serialised as objects.
     * Can't use a constant because of initialisation expression.
     *
     */
    public \stdClass $EMPTY;
    /**
     * @var array|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private mixed $baseUrl;
    private string $assetPathPrefix;

    public function __construct(DocumentationConfig $config = null)
    {
        $this->config = $config ?: new DocumentationConfig(config('scribe', []));
        $this->EMPTY = new \stdClass();
        $this->baseUrl = $this->config->get('base_url') ?? config('app.url');
        // If they're using the default static path,
        // then use '../docs/{asset}', so assets can work via Laravel app or via index.html
        $this->assetPathPrefix = '../docs/';
        if ($this->config->get('type') == 'static'
            && rtrim($this->config->get('static.output_path', ''), '/') != 'public/docs'
        ) {
            $this->assetPathPrefix = './';
        }
    }

    public function generateMdx($groupedEndpoints)
    {
        $destinationFolder = base_path('build/mdx');

        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder, 0777, true);
        }

        foreach ($groupedEndpoints as &$group) {
            $group['subgroups'] = collect($group['endpoints'])->groupBy('metadata.subgroup')->all();
        }

        foreach ($groupedEndpoints as $groupedEndpont) {
            $filename = $destinationFolder . '/' . Str::slug($groupedEndpont['name']).'.mdx';
            $output = view('scribe::mdx.group', [
                'group'            => $groupedEndpont,
                'baseUrl'          => $this->baseUrl,
                'tryItOut'         => $this->config->get('try_it_out'),
                'groupedEndpoints' => $groupedEndpoints,
                'assetPathPrefix'  => $this->assetPathPrefix,
                'metadata'         => $this->getMetadata(),
            ])->render();

            file_put_contents($filename, $output);


            echo $output;

            echo 'write file to '. $filename;
        }
    }

    public function getMetadata(): array
    {
        // todo remove 'links' in future
        $links = []; // Left for backwards compat

        // NB:These paths are wrong for laravel type but will be set correctly by the Writer class
        if ($this->config->get('postman.enabled', true)) {
            $links[] = "<a href=\"{$this->assetPathPrefix}collection.json\">".u::trans("scribe::links.postman")."</a>";
            $postmanCollectionUrl = "{$this->assetPathPrefix}collection.json";
        }
        if ($this->config->get('openapi.enabled', false)) {
            $links[] = "<a href=\"{$this->assetPathPrefix}openapi.yaml\">".u::trans("scribe::links.openapi")."</a>";
            $openApiSpecUrl = "{$this->assetPathPrefix}openapi.yaml";
        }

        $auth = $this->config->get('auth');
        if ($auth) {
            if ($auth['in'] === 'bearer' || $auth['in'] === 'basic') {
                $auth['name'] = 'Authorization';
                $auth['location'] = 'header';
                $auth['prefix'] = ucfirst($auth['in']).' ';
            } else {
                $auth['location'] = $auth['in'];
                $auth['prefix'] = '';
            }
        }

        return [
            'title'                  => $this->config->get('title') ?: config('app.name', '').' Documentation',
            'example_languages'      => $this->config->get('example_languages'),
            'logo'                   => $this->config->get('logo') ?? false,
            'last_updated'           => $this->getLastUpdated(),
            'auth'                   => $auth,
            'try_it_out'             => $this->config->get('try_it_out'),
            "postman_collection_url" => $postmanCollectionUrl ?? null,
            "openapi_spec_url"       => $openApiSpecUrl ?? null,
            'links'                  => array_merge($links,
                ['<a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a>']),
        ];
    }

    protected function getLastUpdated()
    {
        $lastUpdated = $this->config->get('last_updated', 'Last updated: {date:F j, Y}');

        $tokens = [
            "date" => fn($format) => date($format),
            "git"  => fn($format) => match ($format) {
                "short" => trim(shell_exec('git rev-parse --short HEAD')),
                "long" => trim(shell_exec('git rev-parse HEAD')),
                default => throw new InvalidArgumentException("The `git` token only supports formats 'short' and 'long', but you specified $format"),
            },
        ];

        foreach ($tokens as $token => $resolver) {
            $matches = [];
            if (preg_match('#(\{'.$token.':(.+?)})#', $lastUpdated, $matches)) {
                $lastUpdated = str_replace($matches[1], $resolver($matches[2]), $lastUpdated);
            }
        }

        return $lastUpdated;
    }
}